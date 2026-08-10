<?php

  namespace SLTK\Api;

  use SLTK\Domain\Team;
  use SLTK\Domain\TeamInvitation;
  use SLTK\Domain\TeamMember;
  use SLTK\Domain\TeamRequest;
  use WP_REST_Request;
  use WP_REST_Response;

  /**
   * Teams are member-created and member-managed (any logged-in member can create one and becomes
   * its owner), so unlike most controllers in the plugin this is never gated on manage_options —
   * every route just requires being logged in, with ownership/membership checked per-action inside
   * the handlers, the same approach ChampionshipPlanVoteApiController uses for member-facing routes.
   */
  class TeamApiController extends ApiController {

    public function __construct() {
      parent::__construct(ResourceNames::TEAM);
    }

    public function registerRoutes(): void {
      $this->registerRoute('teams', 'GET', [$this, 'canAccess'], [$this, 'listTeams']);
      $this->registerRoute('teams/mine', 'GET', [$this, 'canAccess'], [$this, 'listMyTeams']);
      $this->registerRoute('teams/invitations/mine', 'GET', [$this, 'canAccess'], [$this, 'listMyInvitations']);
      $this->registerRoute('teams/invitations/(?P<invitationId>\\d+)/accept', 'POST', [$this, 'canAccess'], [$this, 'acceptInvitation']);
      $this->registerRoute('teams/invitations/(?P<invitationId>\\d+)/decline', 'POST', [$this, 'canAccess'], [$this, 'declineInvitation']);
      $this->registerRoute('teams/invitations/(?P<invitationId>\\d+)', 'DELETE', [$this, 'canAccess'], [$this, 'withdrawInvitation']);
      $this->registerRoute('teams/requests/(?P<requestId>\\d+)/accept', 'POST', [$this, 'canAccess'], [$this, 'acceptRequest']);
      $this->registerRoute('teams/requests/(?P<requestId>\\d+)/reject', 'POST', [$this, 'canAccess'], [$this, 'rejectRequest']);
      $this->registerRoute('teams/requests/(?P<requestId>\\d+)', 'DELETE', [$this, 'canAccess'], [$this, 'withdrawRequest']);

      $this->registerRoute('teams', 'POST', [$this, 'canAccess'], [$this, 'createTeam']);
      $this->registerRoute('teams/(?P<id>\\d+)', 'GET', [$this, 'canAccess'], [$this, 'getTeam']);
      $this->registerRoute('teams/(?P<id>\\d+)', 'PUT', [$this, 'canAccess'], [$this, 'updateTeam']);
      $this->registerRoute('teams/(?P<id>\\d+)', 'DELETE', [$this, 'canAccess'], [$this, 'deleteTeam']);
      $this->registerRoute('teams/(?P<id>\\d+)/members/(?P<memberId>\\d+)', 'DELETE', [$this, 'canAccess'], [$this, 'removeMember']);
      $this->registerRoute('teams/(?P<id>\\d+)/invitations', 'POST', [$this, 'canAccess'], [$this, 'inviteMember']);
      $this->registerRoute('teams/(?P<id>\\d+)/requests', 'GET', [$this, 'canAccess'], [$this, 'listTeamRequests']);
      $this->registerRoute('teams/(?P<id>\\d+)/requests', 'POST', [$this, 'canAccess'], [$this, 'requestToJoin']);
    }

    public function canAccess(): bool {
      return is_user_logged_in();
    }

    public function listTeams(): WP_REST_Response {
      return $this->execute(function () {
        return ApiResponse::success(array_map(fn($t) => $t->toDto(), Team::list()));
      });
    }

    public function listMyTeams(): WP_REST_Response {
      return $this->execute(function () {
        return ApiResponse::success(array_map(fn($t) => $t->toDto(), Team::listByMemberId(get_current_user_id())));
      });
    }

    public function getTeam(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $team = Team::get($this->getId($request));

        if ($team === null) {
          return ApiResponse::notFound('Team');
        }

        return ApiResponse::success([
          ...$team->toDto(),
          'members' => array_map(fn($m) => $m->toDto(), TeamMember::listByTeamId($team->getId())),
        ]);
      });
    }

    public function createTeam(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $params = $this->getParams($request);

        $team = new Team();
        $team->setName((string)($params['name'] ?? ''));
        $team->setOwnerId(get_current_user_id());
        $team->setLogoUrl((string)($params['logoUrl'] ?? ''));
        $team->setIsAcceptingRequests((bool)($params['isAcceptingRequests'] ?? true));
        $team->save();

        return ApiResponse::created($team->getId());
      });
    }

    public function updateTeam(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $team = Team::get($this->getId($request));

        if ($team === null) {
          return ApiResponse::notFound('Team');
        }

        if (!$team->isOwnedBy(get_current_user_id())) {
          return ApiResponse::forbidden('Only the team owner can make changes.');
        }

        $params = $this->getParams($request);
        $team->setName((string)($params['name'] ?? $team->getName()));
        $team->setLogoUrl((string)($params['logoUrl'] ?? $team->getLogoUrl()));
        $team->setIsAcceptingRequests((bool)($params['isAcceptingRequests'] ?? $team->getIsAcceptingRequests()));
        $team->save();

        return ApiResponse::noContent();
      });
    }

    public function deleteTeam(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $team = Team::get($this->getId($request));

        if ($team === null) {
          return ApiResponse::notFound('Team');
        }

        if (!$team->isOwnedBy(get_current_user_id())) {
          return ApiResponse::forbidden('Only the team owner can delete this team.');
        }

        Team::delete($team->getId());

        return ApiResponse::noContent();
      });
    }

    public function removeMember(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $team = Team::get($this->getId($request));

        if ($team === null) {
          return ApiResponse::notFound('Team');
        }

        $memberId = (int)$request->get_param('memberId');
        $currentUserId = get_current_user_id();

        if (!$team->isOwnedBy($currentUserId) && $memberId !== $currentUserId) {
          return ApiResponse::forbidden('Only the team owner can remove other members.');
        }

        TeamMember::deleteByTeamAndMemberId($team->getId(), $memberId);

        return ApiResponse::noContent();
      });
    }

    public function inviteMember(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $team = Team::get($this->getId($request));

        if ($team === null) {
          return ApiResponse::notFound('Team');
        }

        if (!$team->isOwnedBy(get_current_user_id())) {
          return ApiResponse::forbidden('Only the team owner can invite members.');
        }

        $params = $this->getParams($request);
        $invitation = TeamInvitation::create($team->getId(), (int)$params['memberId']);

        return ApiResponse::created($invitation->getId());
      });
    }

    public function listMyInvitations(): WP_REST_Response {
      return $this->execute(function () {
        return ApiResponse::success(array_map(fn($i) => $i->toDto(), TeamInvitation::listPendingByMemberId(get_current_user_id())));
      });
    }

    public function acceptInvitation(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $invitation = TeamInvitation::get((int)$request->get_param('invitationId'));

        if ($invitation === null) {
          return ApiResponse::notFound('Invitation');
        }

        if ($invitation->getMemberId() !== get_current_user_id()) {
          return ApiResponse::forbidden('This invitation is not addressed to you.');
        }

        $invitation->accept();

        return ApiResponse::noContent();
      });
    }

    public function declineInvitation(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $invitation = TeamInvitation::get((int)$request->get_param('invitationId'));

        if ($invitation === null) {
          return ApiResponse::notFound('Invitation');
        }

        if ($invitation->getMemberId() !== get_current_user_id()) {
          return ApiResponse::forbidden('This invitation is not addressed to you.');
        }

        $invitation->reject();

        return ApiResponse::noContent();
      });
    }

    public function withdrawInvitation(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $invitation = TeamInvitation::get((int)$request->get_param('invitationId'));

        if ($invitation === null) {
          return ApiResponse::notFound('Invitation');
        }

        $team = Team::get($invitation->getTeamId());

        if ($team === null || !$team->isOwnedBy(get_current_user_id())) {
          return ApiResponse::forbidden('Only the team owner can withdraw an invitation.');
        }

        TeamInvitation::delete($invitation->getId());

        return ApiResponse::noContent();
      });
    }

    public function requestToJoin(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $team = Team::get($this->getId($request));

        if ($team === null) {
          return ApiResponse::notFound('Team');
        }

        if (!$team->getIsAcceptingRequests()) {
          return ApiResponse::forbidden('This team is not currently accepting join requests.');
        }

        $joinRequest = TeamRequest::create($team->getId(), get_current_user_id());

        return ApiResponse::created($joinRequest->getId());
      });
    }

    public function listTeamRequests(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $team = Team::get($this->getId($request));

        if ($team === null) {
          return ApiResponse::notFound('Team');
        }

        if (!$team->isOwnedBy(get_current_user_id())) {
          return ApiResponse::forbidden('Only the team owner can view join requests.');
        }

        return ApiResponse::success(array_map(fn($r) => $r->toDto(), TeamRequest::listPendingByTeamId($team->getId())));
      });
    }

    public function acceptRequest(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $teamRequest = TeamRequest::get((int)$request->get_param('requestId'));

        if ($teamRequest === null) {
          return ApiResponse::notFound('Join request');
        }

        $team = Team::get($teamRequest->getTeamId());

        if ($team === null || !$team->isOwnedBy(get_current_user_id())) {
          return ApiResponse::forbidden('Only the team owner can accept join requests.');
        }

        $teamRequest->accept();

        return ApiResponse::noContent();
      });
    }

    public function rejectRequest(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $teamRequest = TeamRequest::get((int)$request->get_param('requestId'));

        if ($teamRequest === null) {
          return ApiResponse::notFound('Join request');
        }

        $team = Team::get($teamRequest->getTeamId());

        if ($team === null || !$team->isOwnedBy(get_current_user_id())) {
          return ApiResponse::forbidden('Only the team owner can reject join requests.');
        }

        $teamRequest->reject();

        return ApiResponse::noContent();
      });
    }

    public function withdrawRequest(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $teamRequest = TeamRequest::get((int)$request->get_param('requestId'));

        if ($teamRequest === null) {
          return ApiResponse::notFound('Join request');
        }

        if ($teamRequest->getMemberId() !== get_current_user_id()) {
          return ApiResponse::forbidden('You can only withdraw your own join request.');
        }

        TeamRequest::delete($teamRequest->getId());

        return ApiResponse::noContent();
      });
    }
  }
