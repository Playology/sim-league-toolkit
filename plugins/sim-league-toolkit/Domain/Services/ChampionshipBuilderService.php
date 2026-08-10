<?php

  namespace SLTK\Domain\Services;

  use DateInterval;
  use DateTime;
  use Exception;
  use SLTK\Core\BannerImageProvider;
  use SLTK\Database\Repositories\RepositoryBase;
  use SLTK\Domain\Championship;
  use SLTK\Domain\ChampionshipEvent;
  use SLTK\Domain\EventClass;
  use SLTK\Domain\EventSession;
  use SLTK\Domain\ValueObjects\ChampionshipBuilderClassPlan;
  use SLTK\Domain\ValueObjects\ChampionshipBuilderPlan;

  class ChampionshipBuilderService {
    private const string TRACK_MASTER_CAR_CLASS = 'FreeForAll';
    private const string RACE_SESSION_TYPE = 'race';

    /**
     * @return string[] Validation error messages, empty if the plan is valid
     */
    public function validate(ChampionshipBuilderPlan $plan): array {
      $errors = [];

      if (trim($plan->name) === '') {
        $errors[] = 'You must provide a name for the Championship.';
      }

      if (trim($plan->description) === '') {
        $errors[] = 'You must provide a description for the Championship.';
      }

      if (count($plan->classes) === 0) {
        $errors[] = 'You must select or add at least one Class.';
      }

      if (count($plan->rounds) < 2) {
        $errors[] = 'You must configure at least two Rounds to form a Championship.';
      }

      if ($plan->isTrackMaster() && $plan->trackMasterTrackId === null) {
        $errors[] = 'You must select a Track for the Championship.';
      }

      foreach ($plan->rounds as $round) {
        if ($plan->isTrackMaster() ? $round->carId === null : $round->trackId === null) {
          $errors[] = $plan->isTrackMaster() ? 'You must select a Car for every Round.' : 'You must select a Track for every Round.';
          break;
        }
      }

      $raceSessionCount = 0;
      foreach ($plan->sessionTemplates as $template) {
        if ($template->sessionType === self::RACE_SESSION_TYPE) {
          $raceSessionCount += $template->count;
        }
      }
      if ($raceSessionCount === 0) {
        $errors[] = 'At least one Race session must be configured.';
      }

      return $errors;
    }

    /**
     * @throws Exception
     */
    public function build(ChampionshipBuilderPlan $plan): Championship {
      return RepositoryBase::transaction(function () use ($plan) {
        $championship = $this->buildChampionship($plan);
        $championship->save();

        $this->createClasses($plan, $championship->getId());
        $this->createRounds($plan, $championship);

        return $championship;
      });
    }

    private function buildChampionship(ChampionshipBuilderPlan $plan): Championship {
      $isTrackMaster = $plan->isTrackMaster();

      $championship = new Championship();
      $championship->setName($plan->name);
      $championship->setDescription($plan->description);
      $championship->setGameId($plan->gameId);
      $championship->setPlatformId($plan->platformId);
      $championship->setChampionshipType($plan->championshipType);
      $championship->setStartDate($plan->startDate);
      $championship->setRuleSetId($plan->ruleSetId);
      $championship->setScoringSetId($plan->scoringSetId);
      $championship->setResultsToDiscard($plan->resultsToDiscard);
      $championship->setMaxEntrants($plan->maxEntrants);
      $championship->setIsActive(false);
      $championship->setBannerImageUrl(BannerImageProvider::getRandomBannerImageUrl());
      $championship->setAllowEntryChange(!$isTrackMaster);
      $championship->setEntryChangeLimit($isTrackMaster ? 0 : $plan->entryChangeLimit);

      if ($isTrackMaster) {
        $championship->setTrackMasterTrackId($plan->trackMasterTrackId);
        $championship->setTrackMasterTrackLayoutId($plan->trackMasterTrackLayoutId);
      }

      return $championship;
    }

    /**
     * @throws Exception
     */
    private function createClasses(ChampionshipBuilderPlan $plan, int $championshipId): void {
      foreach ($plan->classes as $classPlan) {
        $eventClassId = $classPlan->isNew() ? $this->createEventClass($plan, $classPlan) : $classPlan->eventClassId;

        Championship::addChampionshipClass($championshipId, $eventClassId);
      }
    }

    /**
     * @throws Exception
     */
    private function createEventClass(ChampionshipBuilderPlan $plan, ChampionshipBuilderClassPlan $classPlan): int {
      $isTrackMaster = $plan->isTrackMaster();

      $eventClass = new EventClass();
      $eventClass->setName($classPlan->name);
      $eventClass->setGameId($plan->gameId);
      $eventClass->setDriverCategoryId($classPlan->driverCategoryId);
      $eventClass->setCarClass($isTrackMaster ? self::TRACK_MASTER_CAR_CLASS : $classPlan->carClass);
      $eventClass->setIsSingleCarClass(!$isTrackMaster && $classPlan->isSingleCarClass);

      if (!$isTrackMaster && $classPlan->singleCarId !== null) {
        $eventClass->setSingleCarId($classPlan->singleCarId);
      }

      $eventClass->save();

      return $eventClass->getId();
    }

    /**
     * @throws Exception
     */
    private function createRounds(ChampionshipBuilderPlan $plan, Championship $championship): void {
      $isTrackMaster = $plan->isTrackMaster();

      foreach ($plan->rounds as $index => $round) {
        $event = new ChampionshipEvent();
        $event->setChampionshipId($championship->getId());
        $event->setName($plan->name . ' - Round ' . ($index + 1));
        $event->setStartDateTime($this->calculateRoundStartDateTime($plan, $index));
        $event->setIsActive(false);
        $event->setBannerImageUrl(BannerImageProvider::getRandomBannerImageUrl());

        if ($isTrackMaster) {
          $event->setTrackId((int)$championship->getTrackMasterTrackId());
          $event->setTrackLayoutId($championship->getTrackMasterTrackLayoutId());
          $event->setTrackMasterCarId($round->carId);
        } else {
          $event->setTrackId((int)$round->trackId);
          $event->setTrackLayoutId($round->trackLayoutId);
        }

        $event->save();

        $this->createSessionsForEvent($plan, (int)$event->getEventRefId());
      }
    }

    private function calculateRoundStartDateTime(ChampionshipBuilderPlan $plan, int $roundIndex): DateTime {
      $date = (clone $plan->startDate)->add(new DateInterval('P' . ($roundIndex * $plan->eventStartIntervalDays) . 'D'));

      $timeParts = array_map('intval', explode(':', $plan->eventStartTime));
      $date->setTime($timeParts[0] ?? 14, $timeParts[1] ?? 0);

      return $date;
    }

    /**
     * @throws Exception
     */
    private function createSessionsForEvent(ChampionshipBuilderPlan $plan, int $eventRefId): void {
      $sortOrder = 0;

      foreach ($plan->sessionTemplates as $template) {
        for ($occurrence = 0; $occurrence < $template->count; $occurrence++) {
          $session = new EventSession();
          $session->setEventRefId($eventRefId);
          $session->setSessionType($template->sessionType);
          $session->setName(ucfirst($template->sessionType) . ' ' . ($occurrence + 1));
          $session->setSortOrder($sortOrder++);
          $session->setAttributes($template->attributes);

          if (!ChampionshipEvent::saveSession($session)) {
            throw new Exception('Failed to save a Session while building the Championship.');
          }
        }
      }
    }
  }
