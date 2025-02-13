<?php

namespace App\Repositories;

use App\Models\Department;
use App\Models\IssuedItem;
use App\Models\ItemCategory;
use DB;
use Exception;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Throwable;

/**
 * Class IssuedItemRepository
 *
 * @version August 27, 2020, 2:15 pm UTC
 */
class IssuedItemRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'department_id',
        'user_id',
        'issued_by',
        'issued_date',
        'return_date',
        'item_category_id',
        'item_id',
        'quantity',
        'description',
    ];

    /**
     * Return searchable fields
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return IssuedItem::class;
    }

    /**
     * @return mixed
     */
    public function getAssociatedData()
    {
        $data['userTypes'] = Department::all()->pluck('name', 'id')->toArray();
        $data['itemCategories'] = ItemCategory::all()->pluck('name', 'id')->toArray();
        natcasesort($data['userTypes']);
        natcasesort($data['itemCategories']);

        return $data;
    }

    /**
     * @throws Throwable
     */
    public function store($input): bool
    {
        try {
            DB::beginTransaction();

            $issuedItem = IssuedItem::create($input);
            $newItemAvailableQty = $issuedItem->item->available_quantity - $issuedItem->quantity;
            $issuedItem->item()->update(['available_quantity' => $newItemAvailableQty]);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function destroyIssuedItemStock(IssuedItem $issuedItem)
    {
        if ($issuedItem->status == 0) {
            $newItemAvailableQty = $issuedItem->item->available_quantity + $issuedItem->quantity;
            $issuedItem->item()->update(['available_quantity' => $newItemAvailableQty]);
        }
        $this->delete($issuedItem->id);
    }

    public function returnIssuedItem($itemId): bool
    {
        $issuedItem = IssuedItem::whereId($itemId)->first();
        $newItemAvailableQty = $issuedItem->item->available_quantity + $issuedItem->quantity;
        $issuedItem->item()->update(['available_quantity' => $newItemAvailableQty]);
        $issuedItem->update(['return_date' => date('Y-m-d'), 'quantity' => 0, 'status' => IssuedItem::ITEM_RETURNED]);

        return true;
    }
}
