<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response as IlluminateResponse;
use Response;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

/**
     * The default status code.
     *
     * @var int
     */
    protected $statusCode = 200;

    protected $pagination = 10;
    /**
     * The maximum pagination size.
     *
     * @var int The pagination size
     */
    protected $maxLimit = 50;
    /**
     * The minimum pagination size.
     *
     * @var int The pagination size
     */
    protected $minLimit = 1;


    /**
     * Getter for the pagination.
     *
     * @return int The pagination size
     */
    public function getPagination()
    {
        return $this->pagination;
    }

    /**
     * Sets and checks the pagination.
     *
     * @param int $pagination The given pagination
     */
    public function setPagination($pagination)
    {
        $this->pagination = (int) $this->checkPagination($pagination);
    }

    /**
     * Checks the pagination.
     *
     * @param * $pagination The pagination
     *
     * @return int The corrected pagination
     */
    private function checkPagination($pagination)
    {
        // Pagination should be numeric
        if (!is_numeric($pagination)) {
            return $this->pagination;
        }
        // Pagination should not be less than the minimum limitation
        if ($pagination < $this->minLimit) {
            return $this->minLimit;
        }
        // Pagination should not be greater than the maximum limitation
        if ($pagination > $this->maxLimit) {
            return $this->maxLimit;
        }
        // If the pagination is between the min limit and the max limit, return the pagination
        if (!($pagination > $this->maxLimit) && !($pagination < $this->minLimit)) {
            return $pagination;
        }

        // If all fails, return the default pagination
        return $this->pagination;
    }


    public function getAuthenticatedUser()
    {
      try {

        if (! $user = JWTAuth::parseToken()->authenticate()) {
          return response()->json(['user_not_found'], 404);

        }

      } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

        return response()->json(['token_expired'], $e->getStatusCode());


      } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

        return response()->json(['token_invalid'], $e->getStatusCode());

      } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

        return response()->json(['token_absent'], $e->getStatusCode());


      }

      // the token is valid and we have found the user via the sub claim
      return $user;
    }

    public function respondWithFile($filePath, $fileName, $headers = [])
    {
        return Response::download($filePath, $fileName, $headers);
    }

    /**
     * Will result in an array with a paginator.
     *
     * @param LengthAwarePaginator $items   The paginated items
     * @param array                $data    The data
     * @param array                $headers The headers that should be send with the JSON-response
     *
     * @return \Illuminate\Http\JsonResponse The JSON-response with the paginated results
     */
    protected function respondWithPagination(LengthAwarePaginator $items, $data, $headers = [])
    {
        $data = array_merge($data, [
            'pagination' => [
                'total_count'  => $items->total(),
                'total_pages'  => $items->lastPage(),
                'current_page' => $items->currentPage(),
                'limit'        => $items->perPage(),
            ],
        ]);

        return $this->respond($data, $headers);
    }

    /**
     * Will return a response.
     *
     * @param array $data    The given data
     * @param array $headers The given headers
     *
     * @return \Illuminate\Http\JsonResponse The JSON-response
     */
    public function respond($data, $headers = [])
    {
        return Response::json($data, $this->getStatusCode(), $headers);
    }

    /**
     * Getter for the status code.
     *
     * @return int The status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Setter for the status code.
     *
     * @param int $statusCode The given status code
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Will result in a 201 code.
     *
     * @param string $message The given message
     * @param array  $headers The headers that should be send with the JSON-response
     *
     * @return \Illuminate\Http\JsonResponse The JSON-response with the message
     */
    protected function respondCreated($message = 'Item created', $headers = [])
    {
        $this->setStatusCode(IlluminateResponse::HTTP_CREATED);

        return $this->respondWithSuccess($message, $headers);
    }

    /**
     * Will result in an success message.
     *
     * @param string $message The given message
     * @param array  $headers The headers that should be send with the JSON-response
     *
     * @return \Illuminate\Http\JsonResponse The JSON-response with the message
     */
    public function respondWithSuccess($message, $headers = [])
    {
        // return $this->respond([
        //     'success' => [
        //         'message'     => $message,
        //         'status_code' => $this->getStatusCode(),
        //     ],
        // ], $headers);

        return $this->respond(['message'     => $message,'status_code' => $this->getStatusCode()],$headers);

    }

    /**
     * Will result in a 400 error code.
     *
     * @param string $message The given message
     * @param array  $headers The headers that should be send with the JSON-response
     *
     * @return \Illuminate\Http\JsonResponse The JSON-response with the error code
     */
    protected function respondBadRequest($message = 'Bad request', $headers = [])
    {
        $this->setStatusCode(IlluminateResponse::HTTP_BAD_REQUEST);

        return $this->respondWithError($message, $headers);
    }

    /**
     * Will result in an error.
     *
     * @param string $message The given message
     * @param array  $headers The headers that should be send with the JSON-response
     *
     * @return \Illuminate\Http\JsonResponse The JSON-response with the error message
     */
    public function respondWithError($message, $headers = [])
    {
        // return $this->respond([
        //     'error' => [
        //         'message'     => $message,
        //         'status_code' => $this->getStatusCode(),
        //     ],
        // ], $headers);
        return $this->respond(
            ['message'     => $message,'status_code' => $this->getStatusCode()],$headers);
    }

    /**
     * Will result in a 401 error code.
     *
     * @param string $message The given message
     * @param array  $headers The headers that should be send with the JSON-response
     *
     * @return \Illuminate\Http\JsonResponse The JSON-response with the error code
     */
    protected function respondUnauthorized($message = 'Unauthorized', $headers = [])
    {
        $this->setStatusCode(IlluminateResponse::HTTP_UNAUTHORIZED);

        return $this->respondWithError($message, $headers);
    }

    /**
     * Will result in a 403 error code.
     *
     * @param string $message The given message
     * @param array  $headers The headers that should be send with the JSON-response
     *
     * @return \Illuminate\Http\JsonResponse The JSON-response with the error message
     */
    protected function respondForbidden($message = 'Forbidden', $headers = [])
    {
        $this->setStatusCode(IlluminateResponse::HTTP_FORBIDDEN);

        return $this->respondWithError($message, $headers);
    }

    /**
     * Will result in a 404 error code.
     *
     * @param string $message The given message
     *
     * @return \Illuminate\Http\JsonResponse The JSON-response with the error message
     */
    protected function respondNotFound($message = 'Not found')
    {
        $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND);

        return $this->respondWithError($message);
    }

    /**
     * Will result in a 405 error code.
     *
     * @param string $message The given message
     * @param array  $headers The headers that should be send with the JSON-response
     *
     * @return \Illuminate\Http\JsonResponse The JSON-response with the error message
     */
    protected function respondNotAllowed($message = 'Method not allowed', $headers = [])
    {
        $this->setStatusCode(IlluminateResponse::HTTP_METHOD_NOT_ALLOWED);

        return $this->respondWithError($message, $headers);
    }

    /**
     * Will result in a 422 error code.
     *
     * @param string $message The given message
     * @param array  $headers The headers that should be send with the JSON-response
     *
     * @return \Illuminate\Http\JsonResponse The JSON-response with the error code
     */
    protected function respondUnprocessableEntity($message = 'Unprocessable', $headers = [])
    {
        $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY);

        return $this->respondWithError($message, $headers);
    }

    /**
     * Will result in a 429 error code.
     *
     * @param string $message The given message
     * @param array  $headers The headers that should be send with the JSON-response
     *
     * @return \Illuminate\Http\JsonResponse The JSON-response with the error message
     */
    protected function respondTooManyRequests($message = 'Too many requests', $headers = [])
    {
        $this->setStatusCode(IlluminateResponse::HTTP_TOO_MANY_REQUESTS);

        return $this->respondWithError($message, $headers);
    }

    /**
     * Will result in a 500 error code.
     *
     * @param string $message The given message
     * @param array  $headers The headers that should be send with the JSON-response
     *
     * @return \Illuminate\Http\JsonResponse The JSON-response with the error message
     */
    protected function respondInternalError($message = 'Internal Error', $headers = [])
    {
        $this->setStatusCode(IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR);

        return $this->respondWithError($message, $headers);
    }
}