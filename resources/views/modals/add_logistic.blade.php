<div class="modal fade" id="defaultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel"> Add Logistic</h4>
            </div>
            <form  action="{{ route('cms.store', ['page' => 'logistic']) }}" method="POST" enctype="multipart/form-data">
                {{csrf_field()}}
                @method('POST')
            <div class="modal-body">
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Logistic Name" required name="name"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="password" class="form-control" required name="password" placeholder="password"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <label>Upload Logo</label>
                                
                            
                                    <h3>click to upload.</h3>
                  
                                <div class="fallback">
                                    <input type="file" name="image" class="form-control" id="file-input" onchange="loadPreview(this)" multiple/>
                                    <span class="text-danger">{{ $errors->first('image') }}</span>
                                    <div id="thumb-output"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row clearfix">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <div class="form-line">
                                    <input type="email" class="form-control" required name="email" placeholder="Email Address">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <div class="form-line">
                                    <input type="text" class="form-control" required name="phone" placeholder="Phone Number">
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="row clearfix">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <div class="form-line">
                                    <textarea type="text" class="form-control"  name="address">Address</textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary waves-effect m-r-20">SAVE CHANGES</button>
                <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">CLOSE</button>
            </div>
            </form>
        </div>
    </div>
</div>
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
