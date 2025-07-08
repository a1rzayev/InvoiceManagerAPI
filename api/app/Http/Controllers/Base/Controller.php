<?php

namespace App\Http\Controllers\Base;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Invoice Manager API",
 *     description="A comprehensive API for managing invoices, products, and users",
 *     @OA\Contact(
 *         email="admin@invoicemanager.com",
 *         name="API Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * 
 * @OA\Tag(
 *     name="Users",
 *     description="User management endpoints"
 * )
 * @OA\Tag(
 *     name="Products",
 *     description="Product management endpoints"
 * )
 * @OA\Tag(
 *     name="Invoices",
 *     description="Invoice management endpoints"
 * )
 */
abstract class Controller
{
    //
}
