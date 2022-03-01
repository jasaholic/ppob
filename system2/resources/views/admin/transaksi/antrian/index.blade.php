@extends('layouts.admin')

@section('content')
<section class="content-header hidden-xs hidden-sm">
    <h1>Transaksi <small>Antrian Pembelian</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{url('/admin')}}" class="btn-loading"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Transaksi</a></li>
        <li class="active">Antrian Pembelian</li>
    </ol>
</section>
<section class="content">
    <div class="row hidden-xs hidden-sm">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                   <h3 class="box-title">Data Antrian Pembelian</h3>
                </div><!-- /.box-header -->
                <div class="box-body table-responsive">
                    <table id="DataTable" class="table table-hover" style="font-size: 13px;">
                        <thead>
                            <tr class="custom__text-green">
                                <th>No</th>
                                <th>Kode Produk</th>
                                <th>Produk & NoHP</th>
                                <th>IDPel</th>
                                <th>Pengirim</th>
                                <th>Via</th>
                                <th>Tgl Request</th>
                                <th>Tgl Update</th>
                                <th>Status</th>
                                <th colspan="2">#</th>
                            </tr>
                        </thead>
                        <tbody class="read">
                        </tbody>
                    </table>
                </div><!-- /.box-body -->
            </div>
        </div><!-- /.box -->
    </div>
    <div class="row hidden-lg hidden-md">
        <div class="col-xs-12">
            <div class="box"> 
                <div class="box-header">
                    <h3 class="box-title">Data Antrian Pembelian</h3>
                </div><!-- /.box-header -->
                <div class="box-body" style="padding: 0px">
                    <table class="table table-hover">
                        @foreach($antrianMobile as $data)
                        <tr>
                            <td>
                                <div><i class="fa fa-calendar"></i><small> {{date("d M Y", strtotime($data->created_at))}}</small></div>
                                <div style="font-size: 14px;font-weight: bold;">{{$data->produk}}</div>
                                <div>{{$data->target}}</div>
                                <div><code>{{$data->via}}</code></div>
                                <div style="font-size:11px;font-style: italic;">Catatan : {{$data->note}}</div>
                            </td>
                            <td align="right" style="width:35%;">
                                <div><i class="fa fa-clock-o"></i><small> {{date("H:i:s", strtotime($data->created_at))}}</small></div>
                                <div><a href="{{url('/admin/users', $data->user->id)}}" class="btn-loading"><small>{{$data->user->name}}</small></a></div>
                                @if($data->status == 0)
                                <div><span class="label label-warning">PENDING</span></div>
                                @elseif($data->status == 1)
                                <div><span class="label label-success">DIPROSES</span></div>
                                @else
                                <div><span class="label label-danger">GAGAL</span></div>
                                @endif
                                <div style="margin-top:5px;">
                                    <form method="POST" action="{{url('/admin/transaksi/antrian/hapus', $data->id)}}" accept-charset="UTF-8">
                                        <input name="_method" type="hidden" value="DELETE">
                                        <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                        <a href="{{url('/admin/transaksi/antrian', $data->id)}}" class="btn-loading btn btn-primary btn-sm" style="padding: 2px 5px;font-size:10px;">Detail</a>
                                        <button class="btn btn-danger btn-sm" onclick="return confirm('Anda yakin akan menghapus data ?');" type="submit" style="padding: 2px 5px;font-size:10px;">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div><!-- /.box-body -->
                <div class="box-footer" align="center" style="padding-top:13px;">
                   @include('pagination.default', ['paginator' => $antrianMobile])
               </div>
            </div><!-- /.box -->
        </div>
    </div>
</section>
@endsection
@section('js')
<script>
$(document).ready(function() {
    var table = $('#DataTable').DataTable({
        deferRender: true,
        processing: true,
        serverSide: true,
        autoWidth: false,
        info: false,
        ajax:{
            url : "{{ url('/admin/transaksi/antrian/datatables') }}",
            dataType: "json",
            type: "POST",
            data:{ _token: "{{csrf_token()}}"}
        },
        columns:[
                  {data: 'no', width: "50px", sClass: "text-center", orderable: false},
                  {data: 'code', defaulContent: '-' },
                  {data: 'produk', defaulContent: '-' },
                  {data: 'mtrpln', defaulContent: '-'},
                  {data: 'pengirim', defaulContent: '-' },
                  {data: 'via', defaulContent: '-' },
                  {data: 'created_at', defaulContent: '-' },
                  {data: 'updated_at', defaulContent: '-' },
                  {data: 'status', defaulContent: '-' },
                  {data: "action_detail", defaultColumn: "-", orderable: false, searchable: false},
                  {data: "action_hapus", defaultColumn: "-", orderable: false, searchable: false},
                ]
     });});
</script>
@endsection