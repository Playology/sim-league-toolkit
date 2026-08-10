<?php

  namespace SLTK\Domain\Services;

  use Exception;
  use SLTK\Database\Repositories\ChampionshipPlanRepository;
  use SLTK\Domain\ChampionshipPlan;
  use SLTK\Domain\ValueObjects\ChampionshipPlanCarTally;
  use SLTK\Domain\ValueObjects\ChampionshipPlanClassTally;
  use SLTK\Domain\ValueObjects\ChampionshipPlanTrackTally;

  class ChampionshipPlanVotingService {
    /**
     * @throws Exception
     */
    public function castTrackVote(int $planId, int $trackId, int $userId): void {
      $plan = $this->requireOpenPlan($planId);

      if (ChampionshipPlanRepository::hasVotedTrack($planId, $trackId, $userId)) {
        return;
      }

      if (ChampionshipPlanRepository::countTrackVotesForUser($planId, $userId) >= $plan->getMaxTrackVotesPerUser()) {
        throw new Exception('You have already used all of your track votes for this plan.');
      }

      ChampionshipPlanRepository::castTrackVote($planId, $trackId, $userId);
    }

    /**
     * @throws Exception
     */
    public function retractTrackVote(int $planId, int $trackId, int $userId): void {
      $this->requireOpenPlan($planId);

      ChampionshipPlanRepository::retractTrackVote($planId, $trackId, $userId);
    }

    /**
     * @throws Exception
     */
    public function setTrackFavourite(int $planId, int $trackId, int $userId, bool $isFavourited): void {
      if ($isFavourited) {
        ChampionshipPlanRepository::addTrackFavourite($planId, $trackId, $userId);
      } else {
        ChampionshipPlanRepository::removeTrackFavourite($planId, $trackId, $userId);
      }
    }

    /**
     * @throws Exception
     */
    public function castCarVote(int $planId, int $carId, int $userId): void {
      $plan = $this->requireOpenPlan($planId);
      $this->requireTrackMaster($plan);

      if (ChampionshipPlanRepository::hasVotedCar($planId, $carId, $userId)) {
        return;
      }

      if (ChampionshipPlanRepository::countCarVotesForUser($planId, $userId) >= $plan->getMaxCarVotesPerUser()) {
        throw new Exception('You have already used all of your car votes for this plan.');
      }

      $carClass = $this->findCarClass($planId, $userId, $carId);
      if ($carClass !== null && ChampionshipPlanRepository::countCarVotesForUserInClass($planId, $userId, $carClass) >= $plan->getMaxCarsPerClass()) {
        throw new Exception('You have already voted for the maximum number of cars in this class.');
      }

      ChampionshipPlanRepository::castCarVote($planId, $carId, $userId);
    }

    /**
     * @throws Exception
     */
    public function retractCarVote(int $planId, int $carId, int $userId): void {
      $this->requireOpenPlan($planId);

      ChampionshipPlanRepository::retractCarVote($planId, $carId, $userId);
    }

    /**
     * @throws Exception
     */
    public function castClassVote(int $planId, int $planClassId, int $userId): void {
      $plan = $this->requireOpenPlan($planId);
      $this->requireVotableClasses($plan);
      $this->requireClassBelongsToPlan($planId, $planClassId);

      if (ChampionshipPlanRepository::hasVotedClass($planClassId, $userId)) {
        return;
      }

      ChampionshipPlanRepository::castClassVote($planClassId, $userId);
    }

    /**
     * @throws Exception
     */
    public function retractClassVote(int $planId, int $planClassId, int $userId): void {
      $this->requireOpenPlan($planId);
      $this->requireClassBelongsToPlan($planId, $planClassId);

      ChampionshipPlanRepository::retractClassVote($planClassId, $userId);
    }

    /**
     * @throws Exception
     */
    public function suggestClass(int $planId, int $userId, array $classData): int {
      $plan = $this->requireOpenPlan($planId);
      $this->requireVotableClasses($plan);

      $name = trim((string)($classData['name'] ?? ''));
      if ($name === '') {
        throw new Exception('You must provide a name for the class you are suggesting.');
      }

      return ChampionshipPlan::addClass($planId, [
        'eventClassId' => null,
        'name' => $name,
        'carClass' => (string)($classData['carClass'] ?? ''),
        'driverCategoryId' => isset($classData['driverCategoryId']) ? (int)$classData['driverCategoryId'] : null,
        'isSingleCarClass' => (bool)($classData['isSingleCarClass'] ?? false),
        'singleCarId' => isset($classData['singleCarId']) ? (int)$classData['singleCarId'] : null,
        'suggestedByUserId' => $userId,
      ]);
    }

    /**
     * Weighted random track selection for the "pick for me" button: favourited tracks are 3x more
     * likely, matching ACCLT's auto-pick behaviour. Plans vote at track granularity so there's no
     * same-venue duplicate risk to guard against here (unlike ACCLT, which had to explicitly avoid
     * picking two layouts of one venue).
     *
     * @return ChampionshipPlanTrackTally[]
     * @throws Exception
     */
    public function pickTracksForMe(int $planId, int $userId): array {
      $plan = $this->requireOpenPlan($planId);
      $remaining = $plan->getMaxTrackVotesPerUser() - ChampionshipPlanRepository::countTrackVotesForUser($planId, $userId);

      $candidates = array_values(array_filter(
        $this->getTrackTallies($planId, $userId),
        fn(ChampionshipPlanTrackTally $tally) => !$tally->currentUserVoted
      ));

      while ($remaining > 0 && !empty($candidates)) {
        $weighted = [];
        foreach ($candidates as $candidate) {
          $weight = $candidate->currentUserFavourited ? 3 : 1;
          for ($i = 0; $i < $weight; $i++) {
            $weighted[] = $candidate;
          }
        }

        /** @var ChampionshipPlanTrackTally $selected */
        $selected = $weighted[array_rand($weighted)];
        ChampionshipPlanRepository::castTrackVote($planId, $selected->trackId, $userId);

        $candidates = array_values(array_filter($candidates, fn($c) => $c->trackId !== $selected->trackId));
        $remaining--;
      }

      return $this->getTrackTallies($planId, $userId);
    }

    /**
     * Unweighted random car selection for Track Master "pick for me", respecting both the total
     * per-user cap and the per-class cap.
     *
     * @return ChampionshipPlanCarTally[]
     * @throws Exception
     */
    public function pickCarsForMe(int $planId, int $userId): array {
      $plan = $this->requireOpenPlan($planId);
      $this->requireTrackMaster($plan);

      $remaining = $plan->getMaxCarVotesPerUser() - ChampionshipPlanRepository::countCarVotesForUser($planId, $userId);
      $candidates = array_values(array_filter(
        $this->getCarTallies($planId, $userId),
        fn(ChampionshipPlanCarTally $tally) => !$tally->currentUserVoted
      ));
      $classCounts = [];

      while ($remaining > 0 && !empty($candidates)) {
        $index = array_rand($candidates);
        $candidate = $candidates[$index];
        unset($candidates[$index]);
        $candidates = array_values($candidates);

        $classCounts[$candidate->carClass] ??= ChampionshipPlanRepository::countCarVotesForUserInClass($planId, $userId, $candidate->carClass);
        if ($classCounts[$candidate->carClass] >= $plan->getMaxCarsPerClass()) {
          continue;
        }

        ChampionshipPlanRepository::castCarVote($planId, $candidate->carId, $userId);
        $classCounts[$candidate->carClass]++;
        $remaining--;
      }

      return $this->getCarTallies($planId, $userId);
    }

    /**
     * @return ChampionshipPlanTrackTally[]
     * @throws Exception
     */
    public function getTrackTallies(int $planId, int $currentUserId): array {
      return array_map(
        fn($row) => ChampionshipPlanTrackTally::fromStdClass($row),
        ChampionshipPlanRepository::listTrackTallies($planId, $currentUserId)
      );
    }

    /**
     * @return ChampionshipPlanCarTally[]
     * @throws Exception
     */
    public function getCarTallies(int $planId, int $currentUserId): array {
      return array_map(
        fn($row) => ChampionshipPlanCarTally::fromStdClass($row),
        ChampionshipPlanRepository::listCarTallies($planId, $currentUserId)
      );
    }

    /**
     * @return ChampionshipPlanClassTally[]
     * @throws Exception
     */
    public function getClassTallies(int $planId, int $currentUserId): array {
      return array_map(
        fn($row) => ChampionshipPlanClassTally::fromStdClass($row),
        ChampionshipPlanRepository::listClassTallies($planId, $currentUserId)
      );
    }

    /**
     * @throws Exception
     */
    private function requireOpenPlan(int $planId): ChampionshipPlan {
      $plan = ChampionshipPlan::get($planId);
      if ($plan === null) {
        throw new Exception('Championship plan not found.');
      }

      if (!$plan->isOpen()) {
        throw new Exception('Voting is not open for this plan.');
      }

      return $plan;
    }

    /**
     * @throws Exception
     */
    private function requireTrackMaster(ChampionshipPlan $plan): void {
      if (!$plan->isTrackMaster()) {
        throw new Exception('Car voting is only available for Track Master plans.');
      }
    }

    /**
     * @throws Exception
     */
    private function requireVotableClasses(ChampionshipPlan $plan): void {
      if ($plan->getClassesFixed()) {
        throw new Exception('Classes for this plan are fixed by the admin and cannot be voted on.');
      }
    }

    /**
     * @throws Exception
     */
    private function requireClassBelongsToPlan(int $planId, int $planClassId): void {
      if (!ChampionshipPlanRepository::planClassBelongsToPlan($planId, $planClassId)) {
        throw new Exception('That class does not belong to this plan.');
      }
    }

    /**
     * @throws Exception
     */
    private function findCarClass(int $planId, int $currentUserId, int $carId): ?string {
      foreach ($this->getCarTallies($planId, $currentUserId) as $tally) {
        if ($tally->carId === $carId) {
          return $tally->carClass;
        }
      }

      return null;
    }
  }
