<ul class="sidenav-inner py-1">
    <li class="sidenav-item cres-sidenav-item {{ set_active('admin.dashboard') }}">
        <a href="<?php echo route('admin.dashboard') ?>" class="sidenav-link">
            <i class="sidenav-icon uil-home-alt"></i>
            <div><?php echo ('Dashboard') ?></div>
        </a>
    </li>
    {{-- <li class="sidenav-item cres-sidenav-item {{ set_active('admin.spt') }}">
        <a href="<?php echo route('admin.spt') ?>" class="sidenav-link">
            <i class="sidenav-icon uil-dollar-alt"></i>
            <div><?php echo ('SPT') ?></div>
        </a>
    </li> --}}
    <li class="sidenav-item cres-sidenav-item {{ set_active(['admin.master.driver','admin.master.user', 'admin.master.vehicle', 'admin.master.gate', 'admin.master.camera', 'admin.master.garden_planting_period']) }}">
        <a href="javascript:void(0)" class="sidenav-link sidenav-toggle">
            <i class="sidenav-icon uil-folder"></i>
            <div><?php echo ('Master') ?></div>
        </a>
        <ul class="sidenav-menu">
            <li class="sidenav-item cres-sidenav-item {{ set_active('admin.master.driver') }}">
                <a class="sidenav-link" href="<?php echo route('admin.master.driver') ?>">
                    <span><?php echo ('Driver') ?></span>
                </a>
            </li>
            <li class="sidenav-item cres-sidenav-item {{ set_active('admin.master.user') }}">
                <a class="sidenav-link" href="<?php echo route('admin.master.user') ?>">
                    <span><?php echo ('User') ?></span>
                </a>
            </li>
            {{-- <li class="sidenav-item cres-sidenav-item {{ set_active('admin.master.vehicle') }}">
                <a class="sidenav-link" href="<?php echo route('admin.master.vehicle') ?>">
                    <span><?php echo ('Vehicle') ?></span>
                </a>
            </li> --}}
            {{-- <li class="sidenav-item cres-sidenav-item {{ set_active('admin.master.gate') }}">
                <a class="sidenav-link" href="<?php echo route('admin.master.gate') ?>">
                    <span><?php echo ('Gate') ?></span>
                </a>
            </li> --}}
            {{-- <li class="sidenav-item cres-sidenav-item {{ set_active('admin.master.camera') }}">
                <a class="sidenav-link" href="<?php echo route('admin.master.camera') ?>">
                    <span><?php echo ('Camera') ?></span>
                </a>
            </li> --}}
            {{-- <li class="sidenav-item cres-sidenav-item {{ set_active('admin.master.garden_planting_period') }}">
                <a class="sidenav-link" href="<?php echo route('admin.master.garden_planting_period') ?>">
                    <span><?php echo ('Periode Tanam') ?></span>
                </a>
            </li> --}}
        </ul>
    </li>
    <li class="sidenav-item cres-sidenav-item {{ set_active(['admin.farmer_registration.index', 'admin.spk_pengukuran.assign_index', 'admin.garden.index']) }}">
        <a href="javascript:void(0)" class="sidenav-link sidenav-toggle">
            <i class="sidenav-icon uil-user-check"></i>
            <div><?php echo ('Mitra') ?></div>
        </a>
        <ul class="sidenav-menu">
            <li class="sidenav-item cres-sidenav-item {{ set_active('admin.farmer_registration.index') }}">
                <a href="<?php echo route('admin.farmer_registration.index') ?>" class="sidenav-link">
                    <div><?php echo ('Petani') ?></div>
                </a>
            </li>
            <li class="sidenav-item cres-sidenav-item {{ set_active('admin.spk_pengukuran.assign_index') }}">
                <a href="<?php echo route('admin.spk_pengukuran.assign_index') ?>" class="sidenav-link">
                    <div><?php echo ('Spk Pengukuran') ?></div>
                </a>
            </li>
            <li class="sidenav-item cres-sidenav-item {{ set_active('admin.garden.index') }}">
                <a href="<?php echo route('admin.garden.index') ?>" class="sidenav-link">
                    <div><?php echo ('Kebun') ?></div>
                </a>
            </li>
        </ul>
    </li>
    <li class="sidenav-item cres-sidenav-item {{ set_active(['admin.harvest_order_cancel.cancel_index', 'admin.harvest_order.index']) }}">
        <a href="javascript:void(0)" class="sidenav-link sidenav-toggle">
            <i class="sidenav-icon uil-folder"></i>
            <div><?php echo ('Ticket') ?></div>
        </a>
        <ul class="sidenav-menu">
            <li class="sidenav-item cres-sidenav-item {{ set_active('admin.harvest_order.index') }}">
                <a href="<?php echo route('admin.harvest_order.index') ?>" class="sidenav-link">
                    <div><?php echo ('Spp Tiket') ?></div>
                </a>
            </li>
            <li class="sidenav-item cres-sidenav-item {{ set_active('admin.harvest_order_cancel.cancel_index') }}">
                <a href="<?php echo route('admin.harvest_order_cancel.cancel_index') ?>" class="sidenav-link">
                    <div><?php echo ('Interupsi Tiket') ?></div>
                </a>
            </li>
        </ul>
    </li>
    {{-- <li class="sidenav-item cres-sidenav-item {{ set_active('admin.blast_wa.index') }}">
        <a href="<?php echo route('admin.blast_wa.index') ?>" class="sidenav-link">
            <i class="sidenav-icon uil-whatsapp"></i>
            <div><?php echo ('BlastWa') ?></div>
        </a>
    </li> --}}
    <li class="sidenav-item cres-sidenav-item {{ set_active('admin.setting.system') }}">
        <a class="sidenav-link" href="<?php echo route('admin.setting.system') ?>">
            <i class="sidenav-icon uil-cog"></i>
            <div><?php echo ('Setting') ?></div>
        </a>
    </li>
</ul>