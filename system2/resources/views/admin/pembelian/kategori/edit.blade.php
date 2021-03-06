@extends('layouts.admin')

@section('content')
<section class="content-header hidden-xs hidden-sm">
	<h1>Produk <small>Kategori</small></h1>
    <ol class="breadcrumb">
    	<li><a href="{{url('/admin')}}" class="btn-loading"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="Javascript:;">Pembelian</a></li>
        <li><a href="{{route('pembelian-kategori.index')}}" class="btn-loading"> Kategori</a></li>
    	<li class="active">Tambah Kategori</li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-green">
                <div class="box-header">
                    <h3 class="box-title"><a href="{{route('pembelian-kategori.index')}}" class="hidden-lg btn-loading"><i class="fa fa-arrow-left" style="margin-right:10px;"></i></a>Ubah Kategori</h3>
                </div>
                <form role="form" action="{{route('pembelian-kategori.update', $kategori->id)}}" method="post" enctype="multipart/form-data">
                <input name="_method" type="hidden" value="PATCH">
                {{csrf_field()}}
                    <div class="box-body">
                        
                        <div class="form-group{{ $errors->has('product_id') ? ' has-error' : '' }}">
                            <label>ID Kategori (Server) : </label>
                            <input type="text" class="form-control" name="product_id" value="{{$kategori->product_id ?? old('product_id')}}"  placeholder="Masukkan ID Kategori Server">
                            {!! $errors->first('product_id', '<p class="help-block"><small>:message</small></p>') !!}
                        </div>
                        
                        <div class="form-group{{ $errors->has('product_name') ? ' has-error' : '' }}">
                            <label>Nama Kategori : </label>
                            <input type="text" class="form-control" name="product_name" value="{{$kategori->product_name ?? old('product_name')}}" placeholder="Masukkan Nama Kategori">
                            {!! $errors->first('product_name', '<p class="help-block"><small>:message</small></p>') !!}
                        </div>
                        
                        <div class="form-group{{ $errors->has('icon') ? ' has-error' : '' }}">
                            <label>Icon Kategori : </label>
                            <img id="icon" src="{{ $kategori->icon ? asset('assets/images/icon_web/'.$kategori->icon) : ''}}" accept="image/*" class="img-responsive" style="width: 50px; height: 50px;">
                            <input type="file" class="form-control image" name="icon" value="{{$kategori->icon ?? old('icon')}}" onchange="showIcon(this)">
                            {!! $errors->first('icon', '<p class="help-block"><small>:message</small></p>') !!}
                        </div>

                        <div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
                            <label>Status Kategori : </label>
                            <select name="status" class="form-control">
                                <option value="1" {{ $kategori->status == 1 ? 'selected' : '' }}>AKTIF</option>
                                <option value="0" {{ $kategori->status == 0 ? 'selected' : '' }}>TIDAK AKTIF</option>
                            </select>
                            {!! $errors->first('status', '<p class="help-block"><small>:message</small></p>') !!}
                        </div>
                        
                        <div class="form-group{{ $errors->has('sort_product') ? ' has-error' : '' }}">
                            <label>Nomor Urut : </label>
                            <input type="number" class="form-control" name="sort_product" value="{{ $kategori->sort_product ?? old('sort_product') }}" placeholder="Nomor urut kategori">
                            {!! $errors->first('sort_product', '<p class="help-block"><small>:message</small></p>') !!}
                        </div>
                        
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="submit btn btn-primary btn-block">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
        <!--<div class="col-md-6">-->
        <!--    <div class="box box-solid box-penjelasan">-->
        <!--        <div class="box-header">-->
        <!--            <i class="fa fa-text-width"></i>-->
        <!--            <h3 class="box-title">Data Kategori Server (TriPay)</h3>-->
        <!--            <div class="box-tools pull-right box-minus" style="display:none;">-->
        <!--                <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--        <div class="box-body table-responsive no-padding">-->
        <!--            <table class="table table-hover">-->
        <!--                <tr class="text-primary">-->
        <!--                    <th>Product_id</th>-->
        <!--                    <th>Product_name</th>-->
        <!--                    <th>Status</th>-->
        <!--                </tr>-->
                       
        <!--                <tr>-->
                       
        <!--                    <td><label class="label label-success">AKTIF</label></td>-->
                      
        <!--                    <td><label class="label label-success">TIDAK AKTIF</label></td>-->
                           
        <!--                </tr>-->

        <!--            </table>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--</div>-->
    </div>
</section>
@endsection
@section('js')
<script>
     function showIcon(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#icon').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection