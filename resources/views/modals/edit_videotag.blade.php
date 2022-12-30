<div class="modal fade" id="defaultModal2" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel"> Edit Video Tag</h4>
            </div>
            <form  action="" method="POST" enctype="multipart/form-data" id="videotag">
                {{csrf_field()}}
                <input type="hidden" name="_method" value="PUT">
            <div class="modal-body">
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" id="titleEdit" name="name"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                           
                       
                                <div class="drag-icon-cph"> <i class="material-icons">touch_app</i> </div>
                                <h3>Drop files here or click to upload.</h3>
                            <div class="fallback">
                                <input name="icon" type="file" id="titleCode" />
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
