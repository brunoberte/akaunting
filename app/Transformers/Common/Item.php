<?php

namespace App\Transformers\Common;

use App\Transformers\Setting\Category;
use App\Models\Common\Item as Model;
use League\Fractal\TransformerAbstract;

class Item extends TransformerAbstract
{
    /**
     * @var array
     */
    protected $defaultIncludes = ['category'];

    /**
     * @param  Model $model
     * @return array
     */
    public function transform(Model $model)
    {
        return [
            'id' => $model->id,
            'company_id' => $model->company_id,
            'name' => $model->name,
            'sku' => $model->sku,
            'description' => $model->description,
            'sale_price' => $model->sale_price,
            'purchase_price' => $model->purchase_price,
            'quantity' => $model->quantity,
            'category_id' => $model->category_id,
            'picture' => $model->picture,
            'enabled' => $model->enabled,
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
        ];
    }

    /**
     * @param  Model $model
     * @return mixed
     */
    public function includeCategory(Model $model)
    {
        if (!$model->category) {
            return $this->null();
        }

        return $this->item($model->category, new Category());
    }
}
