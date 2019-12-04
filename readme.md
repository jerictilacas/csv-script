# CSV Templating

This script is used for importing csv file when uploading products/ingredients in flex catering website.

## Getting Started

1.  Branch name

    `ingredient-importer`
    
2.  Copy this code to ingredient.php

    `fuel/app/modules/ingredient.php`
    
    
    ```php
    public function action_import()
    {
        $filename = DOCROOT.'import/ingredients.csv';
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
    ```

3.  Note: Make sure to follow the standard header of csv file

    `title
     description
     purchase_unit
     purchase_type
     purchase_unit_cost 
     supplier
     type`
     
     ```    
    If the purchase type is written in abbreviation, rename it into original words'
    example: kg = kilo
             gm = grams
             ml = millimeter`
    The type of the ingredients depends on the purchase type
    example: kilo = grams
             unit = unit
             grams = grams
             liter = millimeter
    ```

4.  Go to cpanel, then paste the code

    `:2083`
    
5. Go to live website, and change the URL 
    


## Ask a question?
    
If you have any query please contact at jericotilacas@gmail.com
