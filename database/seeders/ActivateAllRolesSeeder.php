<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\NewsSource;
use App\Models\AggregatedNews;
use App\Models\Ad;
use App\Models\FooterInfo;
use App\Models\Admin;

class ActivateAllRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create core roles if they don't exist (admin guard)
        $roles = ['Super Admin', 'Admin', 'Editor', 'Author', 'Viewer'];
        
        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'admin',
            ]);
        }

        // Create all permissions used in admin UI if they don't exist
        $permissions = [
            // Category
            'category index',
            'category create',
            'category udpate',
            'category delete',

            // News
            'news index',
            'news status',
            'news all-access',

            // Pages
            'about index',
            'contact index',
            'conatact index',

            // Social / Contact / Home
            'social count index',
            'contact message index',
            'home section index',

            // Ads / Subscribers / Footer
            'advertisement index',
            'subscribers index',
            'footer index',

            // Access management / Settings / Language
            'access management index',
            'setting index',
            'languages index',

            // Aggregator / Realtime (custom)
            'news sources index',
            'aggregated news index',
            'realtime feed index',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'admin',
            ]);
        }

        // Assign permissions to roles
        $superAdminRole = Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();
        if ($superAdminRole) {
            $superAdminRole->syncPermissions(Permission::where('guard_name', 'admin')->get());
        }

        $adminRole = Role::where('name', 'Admin')->where('guard_name', 'admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions([
                'category index',
                'category create',
                'category udpate',
                'category delete',
                'news index',
                'news status',
                'news all-access',
                'about index',
                'contact index',
                'conatact index',
                'social count index',
                'contact message index',
                'home section index',
                'advertisement index',
                'subscribers index',
                'footer index',
                'access management index',
                'setting index',
                'languages index',
                'news sources index',
                'aggregated news index',
                'realtime feed index',
            ]);
        }

        $editorRole = Role::where('name', 'Editor')->where('guard_name', 'admin')->first();
        if ($editorRole) {
            $editorRole->syncPermissions([
                'news index',
                'news status',
                'category index',
            ]);
        }

        $authorRole = Role::where('name', 'Author')->where('guard_name', 'admin')->first();
        if ($authorRole) {
            $authorRole->syncPermissions([
                'news index',
            ]);
        }

        $viewerRole = Role::where('name', 'Viewer')->where('guard_name', 'admin')->first();
        if ($viewerRole) {
            $viewerRole->syncPermissions([
                'news index',
            ]);
        }

        // Ensure primary admin has Super Admin role
        $adminUser = Admin::where('email', 'admin@gmail.com')->first();
        if ($adminUser && $superAdminRole) {
            $adminUser->assignRole($superAdminRole);
        }

        // Ensure all news sources are active
        NewsSource::query()->update(['is_active' => true]);

        // Remove placeholder "test" content from ads
        Ad::query()->where(function ($query) {
            $query->where('home_top_bar_ad', 'test')
                ->orWhere('home_middle_ad', 'test')
                ->orWhere('view_page_ad', 'test')
                ->orWhere('news_page_ad', 'test')
                ->orWhere('side_bar_ad', 'test')
                ->orWhere('home_top_bar_ad_url', 'test')
                ->orWhere('home_middle_ad_url', 'test')
                ->orWhere('view_page_ad_url', 'test')
                ->orWhere('news_page_ad_url', 'test')
                ->orWhere('side_bar_ad_url', 'test');
        })->update([
            'home_top_bar_ad' => null,
            'home_middle_ad' => null,
            'view_page_ad' => null,
            'news_page_ad' => null,
            'side_bar_ad' => null,
            'home_top_bar_ad_status' => 0,
            'home_middle_ad_status' => 0,
            'view_page_ad_status' => 0,
            'news_page_ad_status' => 0,
            'side_bar_ad_status' => 0,
            'home_top_bar_ad_url' => null,
            'home_middle_ad_url' => null,
            'view_page_ad_url' => null,
            'news_page_ad_url' => null,
            'side_bar_ad_url' => null,
        ]);

        // Remove placeholder "test" content from footer info
        FooterInfo::query()->where(function ($query) {
            $query->where('description', 'test')
                ->orWhere('copyright', 'test')
                ->orWhere('logo', '/test');
        })->update([
            'description' => null,
            'copyright' => null,
            'logo' => null,
        ]);

        // Remove mock/seeded articles - keep only real-time aggregated news
        // Articles with no news_source_id are likely mock data
        AggregatedNews::whereNull('news_source_id')->delete();

        $this->command->info('✓ All roles activated and permissions assigned');
        $this->command->info('✓ All news sources are now active for real-time fetching');
        $this->command->info('✓ Mock/seeded articles removed - only real-time data will be displayed');
    }
}
