<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            // 1. Session Flash Messages (untuk SweetAlert / Alert Notifikasi)
            'session' => [
                'status'  => fn () => $request->session()->get('status'),
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                 'newStockOutId' => fn () => $request->session()->get('newStockOutId'),
            ],

            // 2. Authenticated User + Role & Department Context
            'auth' => [
                'user' => $request->user() ? [
                    'id'            => $request->user()->id,
                    'name'          => $request->user()->name,
                    'email'         => $request->user()->email,
                    'department_id' => $request->user()->department_id,
                    'department'    => $request->user()->department ? [
                        'id'   => $request->user()->department->id,
                        'name' => $request->user()->department->name,
                        'code' => $request->user()->department->code,
                    ] : null,
                    'roles'         => $request->user()->getRoleNames(),
                ] : null,
                'permissions' => $request->user() ? $request->user()->getPermissionArray() : [],
            ],

            // 3. Route & Query Params Helper
            'route' => function () use ($request) {
                return [
                    'params' => $request->route() ? $request->route()->parameters() : [],
                    'query'  => $request->all(),
                ];
            },
        ]);
    }
}