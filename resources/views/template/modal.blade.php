<!-- Modal Global -->
<div class="modal fade" id="ajaxModal" role="dialog" aria-labelledby="ajaxModal" data-bs-backdrop="static" data-bs-keyboard="true" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="ajaxModalTitle" data-title="<?php echo get_setting('app_title'); ?>"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="ajaxModalContent">

            </div>
            <div id='ajaxModalOriginalContent' class='hide'>
                <div class="original-modal-body">
                    <div class="circle-loader"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><?php echo __('Close') ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmation -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModal" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 400px;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="confirmationModalTitle"><?php echo __('Delete') . "?"; ?></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="confirmationModalContent" class="modal-body">
                <div class="container-fluid">
                    
                </div>
            </div>
            <div class="modal-footer clearfix">
                <button id="confirmDeleteButton" type="button" class="btn btn-danger" data-bs-dismiss="modal"><?php echo __("Delete"); ?></button>
                <button type="button" class="btn btn-default" data-bs-dismiss="modal"><?php echo __('Cancel'); ?></button>
            </div>
        </div>
    </div>
</div>