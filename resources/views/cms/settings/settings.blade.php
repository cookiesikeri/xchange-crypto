@extends('layouts.app')
@section('title')
Site Settings
@endsection

@section('content')


<section class="content home">
    <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">

        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Site Settings
            </h3>
          </div>
          <!-- /.box-header -->
          <div class="box-body pad">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="card">
                <div class="form-layout">
                <div class="row mg-b-25">
                    <div class="col-md-6 col-sm-12">
                            <form action="{{route('cms.site-settings.update')}}" method="post" enctype="multipart/form-data">
                                {{csrf_field()}}
                                <input type="hidden" name="_method" value="PUT">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label >Site Name</label>
                                    <div class="form-line">
                                    <input type="text" name="site_name" id="site_name" class="form-control{{ $errors->has('site_name') ? ' has-danger has-feedback' : '' }}"  autofocus value="{{$site ? $site->site_name : ''}}">
                                    </div>
                                    @if ($errors->has('site_name'))
                                        <div class="form-control-feedback">
                                            <i class="text-danger icon-cancel-circle2"></i>
                                        </div>
                                        <span class="help-block text-danger">{{ $errors->first('site_name') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label >Hot-line</label>
                                    <div class="form-line">
                                    <input type="text" name="hotline" id="hotline" class="form-control{{ $errors->has('hotline') ? ' has-danger has-feedback' : '' }}"  value="{{$site ? $site->hotline : ''}}">
                                    </div>
                                    @if ($errors->has('hotline'))
                                        <div class="form-control-feedback">
                                            <i class="text-danger icon-cancel-circle2"></i>
                                        </div>
                                        <span class="help-block text-danger">{{ $errors->first('hotline') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label >Hot-line2</label>
                                    <div class="form-line">
                                    <input type="text" name="hotline2" id="hotline2" class="form-control{{ $errors->has('hotline2') ? ' has-danger has-feedback' : '' }}"  value="{{$site ? $site->hotline2 : ''}}">
                                    </div>
                                    @if ($errors->has('hotline2'))
                                        <div class="form-control-feedback">
                                            <i class="text-danger icon-cancel-circle2"></i>
                                        </div>
                                        <span class="help-block text-danger">{{ $errors->first('hotline2') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label >Whatsapp</label>
                                    <div class="form-line">
                                    <input type="text" name="whatsapp" id="whatsapp" class="form-control{{ $errors->has('whatsapp') ? ' has-danger has-feedback' : '' }}"  value="{{$site ? $site->whatsapp : ''}}">
                                    </div>
                                    @if ($errors->has('whatsapp'))
                                        <div class="form-control-feedback">
                                            <i class="text-danger icon-cancel-circle2"></i>
                                        </div>
                                        <span class="help-block text-danger">{{ $errors->first('whatsapp') }}</span>
                                    @endif
                                </div>
                            </div>
                                <div class="col-lg-12">
                                <div class="form-group">
                                    <label >Contact E-mail</label>
                                    <div class="form-line">
                                    <input type="text" name="site_email" id="site_email" class="form-control{{ $errors->has('site_email') ? ' has-danger has-feedback' : '' }}"  value="{{$site ? $site->site_email : ''}}">
                                    </div>
                                    @if ($errors->has('site_email'))
                                        <div class="form-control-feedback">
                                            <i class="text-danger icon-cancel-circle2"></i>
                                        </div>
                                        <span class="help-block text-danger">{{ $errors->first('site_email') }}</span>
                                    @endif
                                </div>
                                </div>
                                <div class="col-lg-12">
                                     <div class="form-group">
                                        <label >Address</label>
                                        <div class="form-line">
                                        <input type="text" name="site_address" id="site_address" class="form-control{{ $errors->has('site_address') ? ' has-danger has-feedback' : '' }}"  value="{{$site ? $site->site_address : ''}}">
                                        </div>
                                        @if ($errors->has('site_address'))
                                            <div class="form-control-feedback">
                                                <i class="text-danger icon-cancel-circle2"></i>
                                            </div>
                                            <span class="help-block text-danger">{{ $errors->first('site_address') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                <div class="form-group">
                                    <label >Facebook Handle</label>
                                    <div class="form-line">
                                    <input type="url" name="facebook" id="facebook" class="form-control{{ $errors->has('facebook') ? ' has-danger has-feedback' : '' }}" value="{{$site ? $site->facebook : ''}}">
                                    </div>
                                    @if ($errors->has('facebook'))
                                        <div class="form-control-feedback">
                                            <i class="text-danger icon-cancel-circle2"></i>
                                        </div>
                                        <span class="help-block text-danger">{{ $errors->first('facebook') }}</span>
                                    @endif
                                </div>
                                </div>
                                <div class="col-lg-12">
                                <div class="form-group">
                                    <label >Twitter Handle</label>
                                    <div class="form-line">
                                    <input type="url" name="twitter" id="twitter" class="form-control{{ $errors->has('twitter') ? ' has-danger has-feedback' : '' }}" value="{{$site ? $site->twitter : ''}}">
                                    </div>
                                    @if ($errors->has('twitter'))
                                        <div class="form-control-feedback">
                                            <i class="text-danger icon-cancel-circle2"></i>
                                        </div>
                                        <span class="help-block text-danger">{{ $errors->first('twitter') }}</span>
                                    @endif
                                </div>
                                </div>
                                <div class="col-lg-12">
                                <div class="form-group">
                                    <label >Linkedin Handle</label>
                                    <div class="form-line">
                                    <input type="url" name="linkedin" id="linkedin" class="form-control{{ $errors->has('linkedin') ? ' has-danger has-feedback' : '' }}" value="{{$site ? $site->linkedin : ''}}">
                                    </div>
                                    @if ($errors->has('linkedin'))
                                        <div class="form-control-feedback">
                                            <i class="text-danger icon-cancel-circle2"></i>
                                        </div>
                                        <span class="help-block text-danger">{{ $errors->first('linkedin') }}</span>
                                    @endif
                                </div>
                                </div>
                                <div class="col-lg-12">
                                <div class="form-group">
                                    <label >Instagram</label>
                                    <div class="form-line">
                                    <input type="url" name="instagram" id="instagram" class="form-control{{ $errors->has('instagram') ? ' has-danger has-feedback' : '' }}" value="{{$site ? $site->instagram : ''}}">
                                    </div>
                                    @if ($errors->has('instagram'))
                                        <div class="form-control-feedback">
                                            <i class="text-danger icon-cancel-circle2"></i>
                                        </div>
                                        <span class="help-block text-danger">{{ $errors->first('instagram') }}</span>
                                    @endif
                                </div>
                                </div>
                                <div class="col-lg-12">
                                <div class="form-group">
                                    <label >Youtube</label>
                                    <div class="form-line">
                                    <input type="url" name="youtube" id="youtube" class="form-control{{ $errors->has('youtube') ? ' has-danger has-feedback' : '' }}" value="{{$site ? $site->youtube : ''}}">
                                    </div>
                                    @if ($errors->has('youtube'))
                                        <div class="form-control-feedback">
                                            <i class="text-danger icon-cancel-circle2"></i>
                                        </div>
                                        <span class="help-block text-danger">{{ $errors->first('youtube') }}</span>
                                    @endif
                                </div>
                                </div>
                                <div class="col-lg-12">
                                <div class="form-group">
                                    <label >Website Logo</label>
                                    <a href="#" tooltip="Add Image" class="btn btn-raised btn-default btn-sm"><i class="zmdi zmdi-camera"></i></a>
                                    <input type="file" name="logo" class="form-control{{ $errors->has('logo') ? ' has-danger has-feedback' : '' }}" id="file-input" onchange="loadPreview(this)" multiple/>
                                    <div id="thumb-output" style="height: 10%"></div>
                                    @if ($errors->has('logo'))
                                        <div class="form-control-feedback">
                                            <i class="text-danger icon-cancel-circle2"></i>
                                        </div>
                                        <span class="help-block text-danger">{{ $errors->first('logo') }}</span>
                                    @endif
                                </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label >App Logo</label>
                                        <a href="#" tooltip="Add Image" class="btn btn-raised btn-default btn-sm"><i class="zmdi zmdi-camera"></i></a>
                                        <input type="file" name="driver_logo" class="form-control{{ $errors->has('driver_logo') ? ' has-danger has-feedback' : '' }}" id="file-input" onchange="loadPreview(this)" multiple/>
                                        <div id="thumb-output" style="height: 10%"></div>
                                        @if ($errors->has('driver_logo'))
                                            <div class="form-control-feedback">
                                                <i class="text-danger icon-cancel-circle2"></i>
                                            </div>
                                            <span class="help-block text-danger">{{ $errors->first('driver_logo') }}</span>
                                        @endif
                                    </div>
                                    </div>
                                <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary btn-sm">Update Settings</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <h4>Default Config Settings</h4>
                            <ul>
                                <li>
                                    <b class="text-uppercase">Site Name: </b> {{$site ? $site->site_name : ''}}
                                    <br><br>
                                </li>
                                <li>
                                        <b class="text-uppercase">Hotline: </b> {{$site ? $site->hotline : ''}}
                                        <br><br>
                                    </li>
                                <li>
                                        <b class="text-uppercase">Hotline2: </b> {{$site ? $site->hotline2 : ''}}
                                        <br><br>
                                </li>
                                <li>
                                    <b class="text-uppercase">Whatsapp: </b> {{$site ? $site->whatsapp : ''}}
                                    <br><br>
                                </li>

                                <li>
                                    <b class="text-uppercase">Site Email: </b> {{$site ? $site->site_email : ''}}
                                    <br><br>
                                </li>
                                 <li>
                                        <b class="text-uppercase">Site Address: </b> {{$site ? $site->site_address : ''}}
                                        <br><br>
                                    </li>
                                <li>
                                    <b class="text-uppercase">Facebook: </b> {{$site ? $site->facebook : ''}}
                                    <br><br>
                                </li>
                                <li>
                                    <b class="text-uppercase">Twitter: </b> {{$site ? $site->twitter : ''}}
                                    <br><br>
                                </li>
                                <li>
                                    <b class="text-uppercase">Linkedin: </b> {{$site ? $site->linkedin : ''}}
                                    <br><br>
                                </li>

                                <li>
                                    <b class="text-uppercase">Instagram: </b> {{$site ? $site->instagram : ''}}
                                    <br><br>
                                </li>
                                <li>
                                    <b class="text-uppercase">Youtube: </b> {{$site ? $site->youtube : ''}}
                                    <br><br>
                                </li>

                                <li>
                                    <b>Website Logo (logo size:207 X 57)</b><br />
                                <p><img src="{{ asset($site->logo) }}" alt="" width="30%" style="float: left; margin-right: 20px;"></p>
                                </li>
                                <br><br>
                                <li>
                                    <b>Driver Logo (logo size:207 X 57)</b><br />
                                <p><img src="{{ asset($site->driver_logo) }}" alt="" width="30%" style="float: left; margin-right: 20px;"></p>
                                </li>


                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.box -->

</div>
<!-- /.col-->
</div>
<!-- ./row -->
</section>
@endsection

@section('javascripts')
<script>

    function loadPreview(input){
        var data = $(input)[0].files;
        $.each(data, function(index, file){
            if(/(\.|\/)(gif|jpe?g|png)$/i.test(file.type)){
                var fRead = new FileReader();
                fRead.onload = (function(file){
                    return function(e) {
                        var img = $('<img/>').addClass('thumb').attr('src', e.target.result); //create image thumb element
                        $('#thumb-output').append(img);
                    };
                })(file);
                fRead.readAsDataURL(file);
            }
        });
    }

</script>



@endsection
