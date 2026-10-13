<?php
namespace App\Http\Controllers\Api\V1;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class MeController extends \App\Http\Controllers\Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $u = $request->user();
        return response()->json(['id' => $u->ulid, 'name' => $u->name, 'roles' => $u->getRoleNames(), 'abilities' => $u->currentAccessToken()->abilities]);
    }
}
