<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Utility;
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         // Super admin


        $Permissions = [
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'manage-plan',
            'create-plan',
            'edit-plan',
            'manage-order',
            'manage-coupon',
            'create-coupon',
            'edit-coupon',
            'lang-manage',
            'lang-change',
            'lang-create',
            'manage-tickets',
            'create-tickets',
            'edit-tickets',
            'delete-tickets',
            'manage-category',
            'create-category',
            'edit-category',
            'delete-category',
            'reply-tickets',
            'manage-setting',
            'manage-faq',
            'create-faq',
            'edit-faq',
            'delete-faq',
            'manage-knowledge',
            'create-knowledge',
            'edit-knowledge',
            'delete-knowledge',
            'manage-knowledgecategory',
            'create-knowledgecategory',
            'edit-knowledgecategory',
            'delete-knowledgecategory',
            'manage-company-settings',
            'manage-group',
            'create-group',
            'edit-group',
            'delete-group',
        ];

        foreach($Permissions as $Permission)
        {
            // $permission = Permission::create(['name' => $Permission]);


            $permission =  new Permission;
            $permission->name = $Permission;
            $permission->save();

        }

         $superAdminRole = Role::create( ['name' => 'Super Admin',]);


        $superAdminPermissions = [
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'manage-plan',
            'create-plan',
            'edit-plan',
            'manage-order',
            'manage-coupon',
            'create-coupon',
            'edit-coupon',
            'manage-setting',
            'lang-manage',
            'lang-create',


        ];
        foreach($superAdminPermissions as $sap)
        {
            $permission = Permission::where(['name' => $sap])->first();
            $superAdminRole->givePermissionTo($permission);
        }

        $superAdmin = User::create(
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'email_verified_at' => date("Y-m-d H:i:s"),
                'password' => Hash::make('1234'),
                'type' => 'Super Admin',
                'parent' => 0,
            ]
        );
        $superAdmin->assignRole($superAdminRole);



        $adminRole        = Role::create(['name' => 'Admin']);
        $adminPermissions = [
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'lang-change',
            'manage-tickets',
            'create-tickets',
            'edit-tickets',
            'delete-tickets',
            'manage-category',
            'create-category',
            'edit-category',
            'delete-category',
            'reply-tickets',
            'manage-setting',
            'manage-faq',
            'create-faq',
            'edit-faq',
            'delete-faq',
            'manage-knowledge',
            'create-knowledge',
            'edit-knowledge',
            'delete-knowledge',
            'manage-knowledgecategory',
            'create-knowledgecategory',
            'edit-knowledgecategory',
            'delete-knowledgecategory',
            'manage-company-settings',
            'create-group'

        ];
        foreach($adminPermissions as $ap)
        {
            $permission = Permission::where(['name' => $ap])->first();
            $adminRole->givePermissionTo($permission);
        }
        $adminUser = User::create(
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'email_verified_at' => date("Y-m-d H:i:s"),
                'password' => Hash::make('1234'),
                'type' => 'Admin',
                'parent' => 1,
                'slug' => 'admin',
                'referral_code' => rand(100000, 999999),
            ]
        );
        $adminUser->assignRole($adminRole);

        $agentRole        = Role::create(['name' => 'Agent']);
        $agentPermissions = [
            'view-users',
            'lang-change',
            'manage-tickets',
            'edit-tickets',
            'reply-tickets',
        ];
        foreach($agentPermissions as $ep)
        {
            $permission = Permission::where(['name' => $ep])->first();
            $agentRole->givePermissionTo($permission);
        }
        $editorUser = User::create(
            [
                'name' => 'Agent',
                'email' => 'agent@example.com',
                'email_verified_at' => date("Y-m-d H:i:s"),
                'password' => Hash::make('1234'),
                'type' => 'Agent',
                'parent' => 2,
            ]
        );
        $editorUser->assignRole($agentRole);


        $freeplan = Plan::create(
            [
                'name' => 'Free Plan',
                'price' => 0,
                'duration' => 'Lifetime',
                'max_agent' => 5,
                'image'=>'free_plan.png',
                'enable_custdomain' => 'on',
                'enable_custsubdomain' => 'on',
                'enable_chatgpt' => 'on',
                'storage_limit' =>  1024
            ]
        );

        $adminUser->assignPlan($freeplan->id);



        Utility::defaultEmail();
        Utility::userDefaultData();
        Utility::languagecreate();

        $data = [
            ['name'=>'local_storage_validation', 'value'=> 'jpg,jpeg,png,xlsx,xls,csv,pdf,svg', 'created_by'=> 1, 'created_at'=> now(), 'updated_at'=> now()],
            ['name'=>'wasabi_storage_validation', 'value'=> 'jpg,jpeg,png,xlsx,xls,csv,pdf,svg', 'created_by'=> 1, 'created_at'=> now(), 'updated_at'=> now()],
            ['name'=>'s3_storage_validation', 'value'=> 'jpg,jpeg,png,xlsx,xls,csv,pdf,svg', 'created_by'=> 1, 'created_at'=> now(), 'updated_at'=> now()],
            ['name'=>'local_storage_max_upload_size', 'value'=> 2048000, 'created_by'=> 1, 'created_at'=> now(), 'updated_at'=> now()],
            ['name'=>'wasabi_max_upload_size', 'value'=> 2048000, 'created_by'=> 1, 'created_at'=> now(), 'updated_at'=> now()],
            ['name'=>'s3_max_upload_size', 'value'=> 2048000, 'created_by'=> 1, 'created_at'=> now(), 'updated_at'=> now()]
        ];

        \DB::table('settings')->insert($data);

    }

}
