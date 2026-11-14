<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ROLE_NAMES = ['super_admin', 'team_lead', 'support_agent'];

    private const USER_MODEL = 'App\\Models\\User';

    /**
     * Move the users.role column into Spatie roles.
     */
    public function up(): void
    {
        $now = now();

        foreach (self::ROLE_NAMES as $name) {
            DB::table('roles')->updateOrInsert(
                ['name' => $name, 'guard_name' => 'web'],
                ['created_at' => $now, 'updated_at' => $now],
            );
        }

        $roleIds = DB::table('roles')
            ->where('guard_name', 'web')
            ->whereIn('name', self::ROLE_NAMES)
            ->pluck('id', 'name');

        DB::table('users')->select(['id', 'role'])->orderBy('id')->each(function (object $user) use ($roleIds): void {
            DB::table('model_has_roles')->insertOrIgnore([
                'role_id' => $roleIds[$user->role] ?? $roleIds['support_agent'],
                'model_type' => self::USER_MODEL,
                'model_id' => $user->id,
            ]);
        });

        Schema::table('users', function ($table) {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }

    /**
     * Restore the users.role column from Spatie roles.
     */
    public function down(): void
    {
        Schema::table('users', function ($table) {
            $table->string('role', 32)->default('support_agent')->index();
        });

        DB::table('users')->select('id')->orderBy('id')->each(function (object $user): void {
            $roleName = DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_type', self::USER_MODEL)
                ->where('model_has_roles.model_id', $user->id)
                ->value('roles.name');

            DB::table('users')->where('id', $user->id)->update(['role' => $roleName ?? 'support_agent']);
        });

        DB::table('model_has_roles')->where('model_type', self::USER_MODEL)->delete();
        DB::table('roles')->whereIn('name', self::ROLE_NAMES)->where('guard_name', 'web')->delete();
    }
};
