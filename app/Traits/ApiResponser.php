<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponser{

    protected function successResponse($data, $code = 200)
	{
		return response()->json([
			'data' => $data
		], $code);
	}
	protected function successResponseWithCustomizedStatus($status,$data, $code = 200)
	{
		return response()->json([
			'status'=>$status,
			'data' => $data
		], $code);
	}

	protected function errorResponse($message = null, $code)
	{
		return response()->json([
			'errors' => $message,
			'data' => []
		], $code);
	}
	protected function errorResponseWithCustomizedStatus($status,$message, $code )
	{
		return response()->json([
			'status'=>$status,
			'errors' => $message,
			'data' => []
		], $code);
	}
	protected function paginatedResponse($paginator,$code=200){
		return response()->json([
			'data'=>[
			'total'=>$paginator->total(),
			'per_page'=>$paginator->perPage(),
			'current_page'=> $paginator->currentPage(),
			'last_page'=>$paginator->lastPage(),
			'data'=>$paginator->items(),
			]
		],$code);
	}

}