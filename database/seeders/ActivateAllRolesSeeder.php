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

class ActivateAllRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create core roles if they don't exist
        $roles = ['Super Admin', 'Admin', 'Editor', 'Author', 'Viewer'];
        
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Create all permissions if they don't exist
        $permissions = [
            'create posts',
            'edit posts',
            'delete posts',
            'view posts',
            'publish posts',
            'manage categories',
            'manage users',
            'manage roles',
            'manage permissions',
            'manage news sources',
            'manage ads',
            'manage settings',
            'view reports',
            'manage content',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Assign permissions to roles
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $superAdminRole->syncPermissions(Permission::all());
        }

        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions([
                'create posts',
                'edit posts',
                'delete posts',
                'view posts',
                'publish posts',
                'manage categories',
                'manage users',
                'manage news sources',
                'manage ads',
                'manage settings',
                'manage content',
                'view reports',
            ]);
        }

        $editorRole = Role::where('name', 'Editor')->first();
        if ($editorRole) {
            $editorRole->syncPermissions([
                'create posts',
                'edit posts',
                'delete posts',
                'view posts',
                'publish posts',
                'manage categories',
                'manage content',
            ]);
        }

        $authorRole = Role::where('name', 'Author')->first();
        if ($authorRole) {
            $authorRole->syncPermissions([
                'create posts',
                'edit posts',
                'view posts',
                'manage content',
            ]);
        }

        $viewerRole = Role::where('name', 'Viewer')->first();
        if ($viewerRole) {
            $viewerRole->syncPermissions([
                'view posts',
            ]);
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
