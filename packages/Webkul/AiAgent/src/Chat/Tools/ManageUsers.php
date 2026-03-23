<?php

namespace Webkul\AiAgent\Chat\Tools;

use Illuminate\Support\Facades\DB;
use Prism\Prism\Tool;
use Webkul\AiAgent\Chat\ChatContext;
use Webkul\AiAgent\Chat\Contracts\PimTool;

class ManageUsers implements PimTool
{
    public function register(ChatContext $context): Tool
    {
        return (new Tool)
            ->as('manage_users')
            ->for('List or inspect admin users.')
            ->withEnumParameter('action', 'Action', ['list', 'details'])
            ->withStringParameter('email', 'User email (for details)')
            ->using(function (string $action = 'list', ?string $email = null): string {
                if ($action === 'list') {
                    $users = DB::table('admins as a')
                        ->leftJoin('roles as r', 'r.id', '=', 'a.role_id')
                        ->select('a.id', 'a.name', 'a.email', 'a.status', 'r.name as role')
                        ->orderBy('a.id')
                        ->limit(50)
                        ->get();

                    return json_encode(['users' => $users->toArray()]);
                }

                if ($action === 'details' && $email) {
                    $user = DB::table('admins as a')
                        ->leftJoin('roles as r', 'r.id', '=', 'a.role_id')
                        ->where('a.email', $email)
                        ->select('a.id', 'a.name', 'a.email', 'a.status', 'a.timezone', 'a.created_at', 'r.name as role', 'r.permission_type')
                        ->first();

                    if (! $user) {
                        return json_encode(['error' => "User '{$email}' not found"]);
                    }

                    return json_encode(['user' => (array) $user]);
                }

                return json_encode(['error' => 'Invalid action']);
            });
    }
}
