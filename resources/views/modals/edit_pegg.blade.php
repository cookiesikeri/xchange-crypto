<div class="modal fade" id="defaultModal2" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel"> Edit Pegg</h4>
            </div>
            <form  action="{{ route('cms.store', ['page' => 'pegg']) }}" method="POST" enctype="multipart/form-data">
                {{csrf_field()}}
                @method('POST')
            <div class="modal-body">
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Label" required name="label"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <select class="form-control show-tick" name="product_id" required>
                            <option value="">-- select Product--</option>
                            @forelse ($products as $role)
                                <option value="{{$role->id}}">{{$role->name}}</option>
                            @empty
                                <option value="0">No roles defined yet.</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <select class="form-control show-tick" name="video_id" required>
                            <option value="">-- select Product Video--</option>
                            @forelse ($producvid as $role)
                                <option value="{{$role->id}}">{{$role->name}}</option>
                            @empty
                                <option value="0">No roles defined yet.</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Label" required name="label"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Label" required name="label"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Label" required name="label"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Label" required name="label"/>
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
