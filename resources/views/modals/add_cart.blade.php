<div class="modal fade" id="defaultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel"> Create Cart</h4>
            </div>
            <form  action="{{ route('cms.store', ['page' => 'cart']) }}" method="POST" enctype="multipart/form-data">
                {{csrf_field()}}
                @method('POST')
            <div class="modal-body">
                <div class="row clearfix">
                    <div class="col-sm-6">
                        <select class="form-control show-tick" name="user_id" required>
                            <option value="">-- select User--</option>
                            @forelse ($users as $role)
                                <option value="{{$role->id}}">{{ucfirst($role->username)}}</option>
                            @empty
                                <option value="0">No data defined yet.</option>
                            @endforelse
                        </select>
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
                </div>
                <div class="row clearfix">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Size" name="size"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Weight" name="weight"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="number" class="form-control" placeholder="Quantity" name="quantity"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Color Code" name="color"/>
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
