@extends('layouts.admin')
@section('title', $title)
@section('content')
<?php
use Cete\Libraries\CTAdmin;
?>
<div class="content-wrapper" id="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <div class="widget-box top">
                <div class="widget-content clearfix">
                    <form action="#" name="form_coa" id="form_coa" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 span6">
                                <div class="form-group">
                                    <label class="form-label control-label">Group COA</label>
                                    <select id="coa_group" name="coa_group" class="form-control select2" style="width: 100%;">
                                        <option value="" disabled selected>-- Pilih --</option>
                                        <?php 
                                            // if(count($data['group_coa']) > 0) {
                                            //     foreach($data['group_coa'] as $group) {
                                            //         $name = $group['coa_code'] . " - " . $group['coa_name'];
                                            //         echo "<option value='{$group['id']}'>{$name}</option>";
                                            //     }
                                            // }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label control-label">No COA</label>
                                    <input class="form-control" type="text" id="coa_code" placeholder="" name="coa_code" autocomplete="off" value="">
                                </div>
                                <div class="form-group">
                                    <label class="form-label control-label">Nama <span style="color:red">*</span></label>
                                    <input class="form-control" type="text" id="coa_name" placeholder="" name="coa_name" autocomplete="off" value="{{ old('coa_name') }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label control-label">Jenis <span style="color:red">*</span></label>
                                    <select id="coa_jenis" name="coa_jenis" class="form-control select2" style="width: 100%;">
                                        <option value="" disabled selected>-- Pilih --</option>
                                        <option value="KAS">KAS</option>
                                        <option value="BANK">BANK</option>
                                        <option value="UMUM">UMUM</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label control-label">Tipe <span style="color:red">*</span></label>
                                    <select id="coa_tipe" name="coa_tipe" class="form-control select2" style="width: 100%;">
                                        <option value="" disabled selected>-- Pilih --</option>
                                        <option value="NERACA">NERACA</option>
                                        <option value="LABA (RUGI)">LABA (RUGI)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 span6">
                                <div class="form-group">
                                    <label class="form-label control-label">Tipe Detail <span style="color:red">*</span></label>
                                    <select id="coa_tipe_detail" name="coa_tipe_detail" class="form-control select2" style="width: 100%;">
                                        <option value="" disabled selected>-- Pilih --</option>
                                        <option value="AKTIVA">AKTIVA</option>
                                        <option value="PASIVA">PASIVA</option>
                                        <option value="PENDAPATAN">PENDAPATAN</option>
                                        <option value="BEBAN">BEBAN</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label control-label">Header/Detail <span style="color:red">*</span></label>
                                    <select id="coa_model" name="coa_model" class="form-control select2" style="width: 100%;">
                                        <option value="" disabled selected>-- Pilih --</option>
                                        <option value="HEADER">HEADER</option>
                                        <option value="DETAIL">DETAIL</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label control-label">Debit/Kredit <span style="color:red">*</span></label>
                                    <select id="coa_normal" name="coa_normal" class="form-control select2" style="width: 100%;">
                                        <option value="" disabled selected>-- Pilih --</option>
                                        <option value="DEBET">DEBET</option>
                                        <option value="KREDIT">KREDIT</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label control-label">Note</label>
                                    <textarea class="form-control" id="note" placeholder="" name="note" autocomplete="off" value="{{ old('note') }}"></textarea>
                                </div>
                            </div>
                            <input type="hidden" id="id" value="" name="id"/>
                        </div>
                        <div class="form-actions clear-both ">
                            <button type="submit" id="cmd-save" name="cmd-save" class="btn btn-primary btn_simpan">
                                <i class="icon fas fa-check"></i> Save changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    
})
</script>
@endsection