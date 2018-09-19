<?php

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

trait ApiResponser{
    /**
     * @param $data
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    private function successResponse($data, $code){
        return response()->json($data, $code);
    }

    /**
     * @param $message
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($message, $code){
        return response()->json(['error'=>$message, 'code'=>$code], $code);
    }

    /**
     * @param Collection $collection
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function showAll(Collection $collection, $code = 200){
        if ($collection->isEmpty()) {
            return $this->successResponse(['data'=>$collection], $code);
        }

        $transformer = $collection->first()->transformer;

        $collection = $this->filterData($collection, $transformer);
        $collection = $this->sortData($collection, $transformer);
        $collection = $this->paginate($collection);
        $collection = $this->transformData($collection, $transformer);
        $collection = $this->cacheResponse($collection);

        return $this->successResponse($collection, $code);
    }

    /**
     * @param Model $model
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function showOne(Model $model, $code = 200){
        $transformer = $model->first()->transformer;
        $data = $this->transformData($model, $transformer);

        return $this->successResponse($data, $code);
    }

    /**
     * @param $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function showMessage($message, $code = 200){
        return $this->successResponse(['data'=>$message], $code);
    }

    /**
     * @param $data
     * @param $transformer
     * @return \Illuminate\Http\JsonResponse
     */
    protected function transformData($data, $transformer){
        $transformation = fractal($data, new $transformer);

        return $transformation->toArray();
    }

    /**
     * @param Collection $collection
     * @param $transformer
     * @return Collection
     */
    protected function sortData(Collection $collection, $transformer){
        if (request()->has('sort_by')) {
            $attribute = $transformer::originalAttribute(request()->sort_by);
            $collection = $collection->sortBy->{$attribute};
        }
        return $collection;
    }

    /**
     * @param Collection $collection
     * @param $transformer
     * @return Collection
     */
    protected function filterData(Collection $collection, $transformer){
        foreach (request()->query() as $query => $value) {
            $attribute = $transformer::originalAttribute($query);

            if (isset($attribute, $value)){
                $collection = $collection->where($attribute, $value);
            }
        }

        return $collection;
    }

    /**
     * @param Collection $collection
     * @return LengthAwarePaginator
     */
    protected function paginate(Collection $collection){
        $rules = [
            'per_page' => 'integer|min:2|max:50'
        ];
        Validator::validate(request()->all(), $rules);

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        if (request()->has('per_page')){
            $perPage = (int)request()->per_page;
        }

        $result = $collection->slice(($page - 1)*$perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator($result, $collection->count(), $perPage, $page, [
            'path'=>LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $paginated->appends(request()->all());

        return $paginated;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function cacheResponse($data){
        $url = request()->url();
        $queryParams = request()->query();

        ksort($queryParams);
        $queryString = http_build_query($queryParams);

        $fullUrl = "{$url}?{$queryString}";

        return Cache::remember($fullUrl, 15/60, function () use ($data){
            return $data;
        });
    }
}