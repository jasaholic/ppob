@extends('layouts.app')
@section('title', 'Dokumentasi API | '.$GeneralSettings->nama_sistem.' - '.$GeneralSettings->motto)
@section('keywords', 'Distributor, Distributor Pulsa, Pulsa, Server Pulsa, Pulsa H2H, Pulsa Murah, distributor pulsa elektrik termurah dan terpercaya, Pulsa Isi Ulang, Pulsa Elektrik, Pulsa Data, Pulsa Internet, Voucher Game, Game Online, Token Listrik, Token PLN, Pascaprabayar, Prabayar, PPOB, Server Pulsa Terpercaya, Bisnis Pulsa Terpercaya, Bisnis Pulsa termurah, website pulsa, Cara Transaksi, Jalur Transaksi, API, H2H', 'Website')
@section('img', asset('assets/images/slider/slider_ke2.png'))
@section('css')
<style>
    pre.api-code {
        color: #000;
        background-color: #fcf6db;
        border: 1px solid #e5e0c6;
    }
    
    pre.api-code > code {
        color: #000;
    }
</style>
@endsection
@section('content')
<!-- Start Slideshow Section -->
<section id="slideshow">
   <div class="container">
      <div class="row">
         <div class="no-slider" style="margin-top: 100px;">
            <div class="animate-block" style="text-align: center;">
            <div class="col-md-6 col-md-offset-3">
               <h1><span id="word-rotating">Dokumentasi API</span></h1>
               <p style="margin-top: 10px;margin-bottom: 80px;">Petunjuk teknis untuk melakukan integrasi API kami</p>
              </div>
            </div> <!--/ animate-block -->
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
</section>
<!-- End Slideshow Section -->

<section id="api-docs" class="grey-bg padding-2x">
    <div class="container">
        <div class="col-md-12">
            <ul class="nav nav-tabs" id="apiDocs" role="tablist">
                <li class="nav-item active">
                    <a class="nav-link active" id="umum-tab" data-toggle="tab" href="#umum" role="tab" aria-controls="umum" aria-selected="false">Umum</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="produk-prabayar-tab" data-toggle="tab" href="#produk-prabayar" role="tab" aria-controls="produk-prabayar" aria-selected="true">Produk Prabayar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="produk-pascabayar-tab" data-toggle="tab" href="#produk-pascabayar" role="tab" aria-controls="produk-pascabayar" aria-selected="true">Produk Pascabayar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="transaksi-tab" data-toggle="tab" href="#transaksi" role="tab" aria-controls="transaksi" aria-selected="false">Transaksi</a>
                </li>
            </ul>
            <div class="tab-content" id="apiDocsContent">
                <div class="tab-pane fade active in" id="umum" role="tabpanel" aria-labelledby="umum-tab" style="padding:20px 0px">
                    @include('api-docs.umum')
                </div>
                <div class="tab-pane fade" id="produk-prabayar" role="tabpanel" aria-labelledby="produk-prabayar-tab" style="padding:20px 0px">
                    @include('api-docs.produk-prabayar')
                </div>
                <div class="tab-pane fade" id="produk-pascabayar" role="tabpanel" aria-labelledby="produk-pascabayar-tab" style="padding:20px 0px">
                    @include('api-docs.produk-pascabayar')
                </div>
                <div class="tab-pane fade" id="transaksi" role="tabpanel" aria-labelledby="transaksi-tab" style="padding:20px 0px">
                    @include('api-docs.transaksi')
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('js')
<script>
    
</script>
@endsection