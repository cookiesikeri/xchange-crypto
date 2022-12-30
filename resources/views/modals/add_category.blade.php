<div class="modal fade" id="defaultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel"> Add Category</h4>
            </div>
            <form  action="{{ route('cms.store', ['page' => 'category']) }}" method="POST" enctype="multipart/form-data">
                {{csrf_field()}}
                @method('POST')
            <div class="modal-body">
                <div class="row clearfix">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Category Name" required name="name"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">                     
                
                                <h3>click to upload Image</h3>
                            <div class="fallback">
                                <input name="image" type="file" >
                            </div>                           
                        </div>
                    </div>
                </div>
                <div class="row clearfix">       
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="form-line">
                                <label>Description </label>
                                <textarea type="text" class="form-control" name="description"></textarea>
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
