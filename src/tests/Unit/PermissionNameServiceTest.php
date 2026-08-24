<?php

namespace Tests\Unit;

use App\Services\PermissionNameService;
use Tests\TestCase;

class PermissionNameServiceTest extends TestCase
{
    private PermissionNameService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PermissionNameService();
    }

    public function test_list_route_becomes_view_permission(): void
    {
        $this->assertSame(
            'master.barang.view',
            $this->service->fromRoute('master.barang.list')
        );
    }

    public function test_show_route_becomes_view_permission(): void
    {
        $this->assertSame(
            'transaksi.penjualan.view',
            $this->service->fromRoute('transaksi.penjualan.show')
        );
    }

    public function test_create_route_remains_create_permission(): void
    {
        $this->assertSame(
            'master.barang.create',
            $this->service->fromRoute('master.barang.create')
        );
    }

    public function test_edit_route_becomes_update_permission(): void
    {
        $this->assertSame(
            'master.barang.update',
            $this->service->fromRoute('master.barang.edit')
        );
    }

    public function test_business_action_without_mapping_is_preserved(): void
    {
        $this->assertSame(
            'transaksi.penjualan.approval',
            $this->service->fromRoute('transaksi.penjualan.approval')
        );
    }

    public function test_route_with_multiple_resource_segments_is_supported(): void
    {
        $this->assertSame(
            'master.harga.customer.view',
            $this->service->fromRoute('master.harga.customer.list')
        );
    }
}
