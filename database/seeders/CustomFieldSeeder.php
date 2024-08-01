<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomField;
class CustomFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $status = 1;
        $created_by = 2;

        $fields = [
            [
                "name" => __("Name"),
                "type" => "text",
                "placeholder" => __("Name"),
                "width" => "6",
                "custom_id" => "1",
            ],
            [
                "name" => __("Email"),
                "type" => "email",
                "placeholder" => __("Email"),
                "width" => "6",
                "custom_id" => "2",
            ],
            [
                "name" => __("Category"),
                "type" => "select",
                "placeholder" => __("Select Category"),
                "width" => "6",
                "custom_id" => "3",
            ],
            [
                "name" => __("Subject"),
                "type" => "text",
                "placeholder" => __("Subject"),
                "width" => "6",
                "custom_id" => "4",
            ],
            [
                "name" => __("Description"),
                "type" => "textarea",
                "placeholder" => __("Description"),
                "width" => "12",
                "custom_id" => "5",
            ],
            [
                "name" => __("Attachments"),
                "type" => "file",
                "placeholder" => __("You can select multiple files"),
                "width" => "12",
                "custom_id" => "6",
            ],
            [
                "name" => __("Priority"),
                "type" => "select",
                "placeholder" => __("Select Priority"),
                "width" => "6",
                "custom_id" => "7",
            ],
        ];

        foreach($fields as $order => $field) {
            $f = $field;
            $f['order'] = $order;
            $f['status'] = $status;
            $f['created_by'] = $created_by;

            CustomField::create($f);
        }
    }
}
