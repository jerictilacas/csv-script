<?php

/**
 * The Welcome Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 *
 * @package  app
 * @extends  Controller
 */

namespace Ingredient;

class Controller_Admin_Ingredient extends \Admin\Controller_Base
{

    public function action_import()
    {
        $filename = DOCROOT.'import/acs_ingredients1.csv';
        $handle = fopen($filename, 'r');
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle, 10000, ",")) !== false)
        {
            $row_data = array_map('trim', $row);
            $data = array_combine(array_map('trim', $header), $row_data);

            $supplier = \DB::query(\DB::expr("SELECT users_groups.user_id FROM users_metadata
                          left join users_groups on users_groups.user_id = users_metadata.user_id 
                          where users_metadata.business_name = '{$data['supplier']}' and users_groups.group_id = 18"))->execute();

            $insert = [];
            // 4001 = flex catering
            $insert['supplier'] = isset($supplier[0]['user_id'])? $supplier[0]['user_id']: '2475';
            $insert['sku'] = strtoupper('SI-' . substr(md5(uniqid()), 0, 6));
            $insert['title'] = $data['title'];
            $insert['body'] = $data['description'];
            $insert['enable_to_purchase'] = 1;
            $insert['purchase_qty'] = $data['purchase_unit']? $data['purchase_unit']: 1;
            $insert['purchase_type'] = $data['purchase_type'];
            $insert['purchase_cost'] = $data['purchase_unit_cost'];
            $insert['type'] = $data['type'];

            $purchase_qty = $data['purchase_unit']? $data['purchase_unit']: 1;

            if($insert['purchase_type'] == 'litre')
            {
                $purchase_qty = $purchase_qty * 1000;
            }

            if($insert['purchase_type'] == 'kilo')
            {
                $purchase_qty = $purchase_qty * 1000;
            }

            $insert['cost'] = $data['purchase_unit_cost']/$purchase_qty;

            $item = Model_Ingredient::forge($insert);
            try
            {
                $item->save();
            }
            catch (\Database_Exception $e)
            {
                continue;
            }
            catch (\Exception $e)
            {
                continue;
            }

        }
    }

}