<html>
  <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Struk Pembayaran Tagihan</title>
  </head>

  <style type="text/css">
/* http://meyerweb.com/eric/tools/css/reset/ 
   v2.0 | 20110126
   License: none (public domain)
*/

html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
b, u, i, center,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td,
article, aside, canvas, details, embed, 
figure, figcaption, footer, header, hgroup, 
menu, nav, output, ruby, section, summary,
time, mark, audio, video {
	margin: 0;
	padding: 0;
	border: 0;
	/*font-size: 100%;*/
	/*font: inherit;*/
	vertical-align: baseline;
}
/* HTML5 display-role reset for older browsers */
article, aside, details, figcaption, figure, 
footer, header, hgroup, menu, nav, section {
	display: block;
}
body {
	line-height: 1;
}
ol, ul {
	list-style: none;
}
blockquote, q {
	quotes: none;
}
blockquote:before, blockquote:after,
q:before, q:after {
	content: '';
	content: none;
}
table {
	/*border-collapse: collapse;*/
	border-spacing: 3;
}
</style>

<style type="text/css">

#content{
	font-family: "Courier New", Courier, "Lucida Sans Typewriter", "Lucida Typewriter", monospace;
	margin:5px;
	font-size: 10px;
	padding: 5px;
}

.info{
	text-transform: uppercase;
}

.service{
	margin-bottom:5px;
}

.tex-col{
	padding: 10px;
	text-align: center;
}
  
}

hr {
  border:none;
  border-top:1px dotted black;
  /*color:#fff;*/
  /*background-color:#fff;*/
  height:1px;
  width:100%;
}

  </style>
  <body>
<br>
<div id="content">
      <div class="logo"></div>
      <div class="info"> 
      	<center>
        	<h4>{{ in_array($user->roles[0]->id, [3,4]) ? $user->name : $GeneralSettings->nama_sistem }}</h4><br>
        	<h4>Struk Pembayaran Tagihan {{$trx->produk}}</h4>
    	</center>
      </div>
		<hr>
			<table>
			    @if( !in_array($user->roles[0]->id, [3,4]) )
				  <tr>
					<td>OUTLET :</td>
				  </tr>
				  <tr>
				      <td>{{isset($user->name)?$user->name:'-'}}</td>
				  </tr>
				  <tr>
				      <td></td>
				  </tr>
			    @endif
				  <tr>
					<td>TAGIHAN ID : </td>
				  </tr>
				  <tr>
				     <td>{{isset($trx->tagihan_id)?$trx->tagihan_id:'-'}}</td>
				  </tr>
				  <tr>
				      <td></td>
				  </tr>
				  <tr>
					<td>TANGGAL :</td>
				  </tr>
				  <tr>
				     <td>{{date("d M Y", strtotime($trx->created_at))}} {{date("H:i:s", strtotime($trx->created_at))}}</td>
				  </tr>
				  <tr>
				      <td></td>
				  </tr>
			</table>
		<hr>
			<table class="data">
			  <tr>
			  	<td>TAGIHAN : </td>
			  </tr>
			  <tr>
			      <td>{{isset($trx->produk)?$trx->produk:'-'}}</td>
			  </tr>
			  <tr>
			      <td></td>
			  </tr>
			  <tr>
			  	<td>NAMA PELANGGAN : </td>
			  </tr>
			  <tr>
			      <td>{{isset($tagihan->nama)?$tagihan->nama:'-'}}</td>
			  </tr>
			  <tr>
			      <td></td>
			  </tr>
			  <tr>
			  	<td>NO.REK/ID.PELANGGAN : </td>
			  </tr>
                <tr>
                    <td>{{isset($trx->mtrpln)?$trx->mtrpln:'-'}}</td>
                </tr>
                <tr>
                    <td></td>
                </tr>
			  <tr>
			  	<td>BL/TH : </td>
			  </tr>
			  <tr>
			      <td>{{isset($tagihan->periode)?$tagihan->periode:'-'}}<</td>
			  </tr>
			  <tr>
			      <td></td>
			  </tr>
			  <tr>
			  	<td>NO.HP : </td>
			  </tr>
			  <tr>
			      <td>{{isset($trx->target)?$trx->target:'-'}}</td>
			  </tr>
			  <tr>
			      <td></td>
			  </tr>
			  <tr style="display:none;">
			  	<td>NO.REF : </td>
			  </tr>
			  <tr>
			      <td>{{isset($trx->token)?$trx->token:'-'}}</td>
			  </tr>
			  <tr>
			      <td></td>
			  </tr>
			  <tr>
				<td>STATUS : </td>
			  </tr>
			  <tr>
			      <td>TRX {{($trx->status == 0) ? "PROSES" :(($trx->status == 1) ? "BERHASIL" :(($trx->status == 2) ? "GAGAL" :(($trx->status == 3) ? "REFUND" : "-")))}}</td>
			  </tr>
			  <tr>
			      <td></td>
			  </tr>
			  <tr>
				<td>JUMLAH TAGIHAN : </td>
			  </tr>
			  <tr>
			      <td><b>RP {{number_format($trx->harga_default, 0, '.', '.')}}</b></td>
			  </tr>
			  <tr>
			      <td></td>
			  </tr>
		  	  <tr class="service">
				<td>BIAYA ADMIN : </td>
			  </tr>
			  <tr>
			      	<td><b>RP {{number_format($trx->harga_markup, 0, '.', '.')}}</b></td>
			  </tr>
			  <tr>
			      <td></td>
			  </tr>
			  <tr class="service">
				<td>TOTAL BAYAR : </td>
			  </tr>
			  <tr>
			      <td><b>RP {{number_format($trx->total, 0, '.', '.')}}</b></td>
			  </tr>
			  <tr>
			      <td></td>
			  </tr>
			</table>
		<hr>
			<p align="center">
				<i>
				TERIMA KASIH<br>
				"Informasi Hubungi Admin {{ $user->roles[0]->id == 4 ? $user->name : $GeneralSettings->nama_sistem }}" 
				</i>
			</p>
		<hr>
</div>
  </body>
</html>