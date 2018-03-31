@extends('layouts.app_pelanggan')
@section('content')
<?php
$settingFooter = \App\SettingFooter::select()->
first();
?>
<style type="text/css">
    .list-produk {

        padding-left: 5px;
        padding-right: 10px;

    }
    .card .card-image{

        height: auto; /*this makes sure to maintain the aspect ratio*/
        margin-top: 0px;
    }
    .card-pricing {
        margin-bottom: 20px;
    }
    .tombolBeli {
        padding: 10px 0px;
        margin:0px;
    }
    .card-pricing .card-content {
        padding: 10px !important;
    } 
    .card .footer {
        margin-top: 0px;
        font-family: Helvetica,Arial,sans-serif;
        font-weight: 400;
        line-height:1.2em;
        text-decoration: none;
        font-size:15px;
    }
    .buttonColor{ 
        background-color: #FFCA28; 
    }
    @font-face {
      font-family: "San Francisco";
      font-weight: 200;
      src: url("//applesocial.s3.amazonaws.com/assets/styles/fonts/sanfrancisco/sanfranciscodisplay-thin-webfont.woff2");
  }
     /*style untuk kategori*/ 
      .nav .open>a, .nav .open>a:hover, .nav .open>a:focus{
        background-color: #2ac326;
      }
      .card .card-content {
        padding: 0px 30px;
      }
      .card-form-horizontal .card-content {
        padding-left: 15px;
        padding-right: 15px;
        padding-top: 10px;
    }
    .nav-tabs {
        background: #2ac326;
        border: 0;
        border-radius: 3px;
        padding: 9 15px;
    }
    /*style untuk kategori*/

  .flexFont {
    @if(Agent::isMobile())
    height:3em;
    @else
    height:3em;
    @endif
    padding:1%;
    margin: 5px;

}
.btnWarung {
    padding:1%;
    margin: 0px;
}

.smaller {
    font-size: 0.7em;
    background-color:red;
    width: 10em;
}
.page-header.header-small {
    height: 35vh;
    min-height: 35vh;
}
.ecommerce-page .page-header .container {
    @if(Agent::isMobile())
    padding-top: 7vh;
    @else
    padding-top: 10vh;
    @endif
}
h4 {
    @if(Agent::isMobile())
    font-size: 1.2em;
    line-height: 1.4em;
    margin: 20px 0 10px;
    @endif
}
.panel .panel-heading {
    background-color: transparent;
    border-bottom: 2px solid #ddd;
    padding: 5px 0px 5px 0px;
}
.scrollable-menu {
    height: auto;
    max-height: 250px;
    overflow-x: hidden;
}
.nav-tabs > li > a{
    font-size: 20px;
}
</style>

<style type="text/css">
</style> 
    <div class="page-header header-filter header-mediums" data-parallax="true" style="{!! $foto_latar_belakang !!}">
    </div> 
@if (Agent::isMobile())  
        <div class="main main-raised">
            <div class="container">
                <h3 class="title text-center">
                    {!! $nama_kategori !!}
                </h3>
                <div class="row">
                <div class="col-sm-12">
                <div class="card card-raised card-form-horizontal">
                <div class="card-content">  
                    {!! Form::open(['url' => route('daftar_produk.pencarian'),'method' => 'get', 'class'=>'form-horizontal']) !!}
                    <div class="row">
                        <div class="col-sm-10">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="material-icons">
                                        search
                                    </i>
                                </span> 
                                <input class="form-control" id="cari_produk" name="search" placeholder="Cari Pakaian" type="text" value=""/> 
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <button class="btn btn-block buttonColor" type="submit">
                                Cari
                            </button>
                        </div>
                        </div>
                     </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
                <div class="row">
                    <div class="col-md-12">
                        <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- Menampilkan Produk -->
                                    <span id="span-produk">
                                        {!! $daftar_produk !!}
                                    </span>
                                </div>
                                <div class="col-md-12">
                                    {{$produk_pagination}}
                                </div>
                            </div>
                        </br>
                    </div> 
                </div>
            </div>
        </div>
        <!-- end-main-raised -->
    @else   
        <div class="main main-raised">
            <div class="container">
                <h3 class="title text-center">
                    {!! $nama_kategori !!}
                </h3>
                <div class="row">
                <div class="col-sm-12">
                <div class="card card-raised card-form-horizontal">
                <div class="card-content">  
                    {!! Form::open(['url' => route('daftar_produk.pencarian'),'method' => 'get', 'class'=>'form-horizontal']) !!}
                    <div class="row">
                        <div class="col-sm-10">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="material-icons">
                                        search
                                    </i>
                                </span> 
                                <input class="form-control" id="cari_produk" name="search" placeholder="Cari Pakaian" type="text" value=""/> 
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <button class="btn btn-block buttonColor" type="submit">
                                Cari
                            </button>
                        </div>
                        </div>
                     </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
                <div class="row">
                    <div class="col-md-12">
                        <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- Menampilkan Produk -->
                                    <span id="span-produk">
                                        {!! $daftar_produk !!}
                                    </span>
                                </div>
                                <div class="col-md-12">
                                    {{$produk_pagination}}
                                </div>
                            </div>
                        </br>
                    </div> 
                </div>
            </div>
        </div>
        <!-- end-main-raised -->
        @endif

        @endsection
        @section('scripts')
        <script type="text/javascript">
            flexFont = function () {
                @if(Agent::isMobile())
                var divs = document.getElementsByClassName("flexFont");
                for(var i = 0; i < divs.length; i++) {
                    var relFontsize = divs[i].offsetWidth*0.1;
                    divs[i].style.fontSize = relFontsize+'px';
                }
                @else
                var divs = document.getElementsByClassName("flexFont");
                for(var i = 0; i < divs.length; i++) {
                    var relFontsize = divs[i].offsetWidth*0.06;
                    divs[i].style.fontSize = relFontsize+'px';
                }

                @endif
                @if(Agent::isMobile())
                var divs = document.getElementsByClassName("flexFontWarung");
                for(var i = 0; i < divs.length; i++) {
                    var relFontsize = divs[i].offsetWidth*0.15;
                    divs[i].style.fontSize = relFontsize+'px';
                }
                @else
                var divs = document.getElementsByClassName("flexFontWarung");
                for(var i = 0; i < divs.length; i++) {
                    var relFontsize = divs[i].offsetWidth*0.1;
                    divs[i].style.fontSize = relFontsize+'px';
                }

                @endif
            };

            window.onload = function(event) {
                flexFont();
            };
            window.onresize = function(event) {
                flexFont();
            };
        </script>
        @endsection
    </div>
</div>