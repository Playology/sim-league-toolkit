<?php

  namespace SLTK\Api;

  use WP_REST_Response;

  class ApiResponse
  {
    public static function success($data = null, int $status = 200): WP_REST_Response
    {
      return new WP_REST_Response($data, $status);
    }

    public static function created(int $id): WP_REST_Response
    {
      return new WP_REST_Response(['id' => $id], 201);
    }

    public static function noContent(): WP_REST_Response
    {
      return new WP_REST_Response(null, 204);
    }

    public static function notFound(string $resource = 'Resource'): WP_REST_Response
    {
      return new WP_REST_Response([
        'code' => 'not_found',
        'message' => "{$resource} not found"
      ], 404);
    }

    public static function forbidden(string $message = 'You do not have permission to perform this action.'): WP_REST_Response
    {
      return new WP_REST_Response([
        'code' => 'forbidden',
        'message' => $message
      ], 403);
    }

    public static function badRequest(string $message, array $errors = []): WP_REST_Response
    {
      $response = [
        'code' => 'bad_request',
        'message' => $message
      ];

      if (!empty($errors))
      {
        $response['errors'] = $errors;
      }

      return new WP_REST_Response($response, 400);
    }

    public static function validationFailed(array $errors): WP_REST_Response
    {
      return new WP_REST_Response([
        'code' => 'validation_failed',
        'message' => 'One or more fields are invalid',
        'errors' => $errors
      ], 422);
    }

    public static function serverError(string $message = 'An unexpected error occurred'): WP_REST_Response
    {
      return new WP_REST_Response([
        'code' => 'server_error',
        'message' => $message
      ], 500);
    }
  }