<?php

namespace Riari\Forum\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Riari\Forum\API\Cache;
use Riari\Forum\Models\Category;

class CategoryController extends BaseController
{
    /**
     * Return the model to use for this controller.
     *
     * @return Category
     */
    protected function model()
    {
        return new Category;
    }

    /**
     * Return the translation file name to use for this controller.
     *
     * @return string
     */
    protected function translationFile()
    {
        return 'categories';
    }

    /**
     * GET: Return an index of categories.
     *
     * @param  Request  $request
     * @return JsonResponse|Response
     */
    public function index(Request $request)
    {
        $categories = $this->model()->withRequestScopes($request);

        if ($request->input('include_deleted') && Gate::allows('deleteCategories')) {
            $categories = $categories->withTrashed();
        }

        $categories = $request->input('paginate') ? $categories->paginate() : $categories->get();

        return $this->response($categories);
    }

    /**
     * GET: Return a model by ID.
     *
     * @param  int  $id
     * @param  Request  $request
     * @return JsonResponse|Response
     */
    public function fetch($id, Request $request)
    {
        $model = $this->model();

        $model = Gate::allows('viewTrashedCategories') ? $model->withTrashed()->find($id) : $model->find($id);

        if (is_null($model) || !$model->exists) {
            return $this->notFoundResponse();
        }

        return $this->response($model);
    }

    /**
     * POST: Create a new category.
     *
     * @param  Request  $request
     * @return JsonResponse|Response
     */
    public function store(Request $request)
    {
        $this->authorize('createCategories');

        $this->validate($request, [
            'title'     => ['required'],
            'weight'    => ['required']
        ]);

        $category = $this->model()->create($request->only(['category_id', 'title', 'weight', 'allows_threads']));

        return $this->response($category, 201);
    }

    /**
     * PATCH: Restore a category.
     *
     * @param  int  $id
     * @param  Request  $request
     * @return JsonResponse|Response
     */
    public function restore($id, Request $request)
    {
        $this->authorize('deleteCategories');

        return parent::restore($id, $request);
    }

    /**
     * PATCH: Move a category.
     *
     * @param  int  $id
     * @param  Request  $request
     * @return JsonResponse|Response
     */
    public function move($id, Request $request)
    {
        $this->authorize('moveCategories');

        $this->validate($request, ['category_id' => ['required']]);

        $category = $this->model()->find($id);

        return ($category)
            ? $this->updateAttributes($category, ['category_id' => $request->input('category_id')])
            : $this->notFoundResponse();
    }

    /**
     * PATCH: Rename a category.
     *
     * @param  int  $id
     * @param  Request  $request
     * @return JsonResponse|Response
     */
    public function rename($id, Request $request)
    {
        $this->authorize('renameCategories');

        $this->validate($request, ['title' => ['required']]);

        $category = $this->model()->find($id);

        return ($category)
            ? $this->updateAttributes($category, ['title' => $request->input('title')])
            : $this->notFoundResponse();
    }

    /**
     * PATCH: Reorder a category.
     *
     * @param  int  $id
     * @param  Request  $request
     * @return JsonResponse|Response
     */
    public function reorder($id, Request $request)
    {
        $this->authorize('moveCategories');

        $this->validate($request, ['weight' => ['required']]);

        $category = $this->model()->find($id);

        return ($category)
            ? $this->updateAttributes($category, ['weight' => $request->input('weight')])
            : $this->notFoundResponse();
    }
}
