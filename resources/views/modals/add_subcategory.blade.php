<div class="modal fade" id="defaultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel"> Add SubCategory</h4>
            </div>
            <form  action="{{ route('cms.store', ['page' => 'subcategory']) }}" method="POST" enctype="multipart/form-data">
                {{csrf_field()}}
                @method('POST')
            <div class="modal-body">
                <div class="row clearfix">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="SubCategory Name" required name="name"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <label>Upload Photo</label>
                                <input type="file" class="form-control" name="image"/>
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
                <div class="row clearfix">   
                    <div class="col-sm-12">
                        <select class="form-control show-tick" name="category_id" required>
                            <option value="">-- select Category--</option>
                            @forelse ($contacts as $role)
                                <option value="{{$role->id}}">{{$role->name}}</option>
                            @empty
                                <option value="0">No data defined yet.</option>
                            @endforelse
                        </select>
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
