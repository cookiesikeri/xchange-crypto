<div class="modal fade" id="defaultModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel"> Add Shop</h4>
            </div>
            <form  action="{{ route('cms.store', ['page' => 'shop']) }}" method="POST" enctype="multipart/form-data">
                {{csrf_field()}}
                @method('POST')
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Company Name" required name="company_name"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                        <select class="form-control show-tick" name="user_id" required>
                            <option value="">-- select User--</option>
                            @forelse ($users as $role)
                                <option value="{{$role->id}}">{{$role->username}}</option>
                            @empty
                                <option value="0">No roles defined yet.</option>
                            @endforelse
                        </select>
                        </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder=" Phone" required name="phone"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="email" class="form-control" placeholder=" Email" required name="email"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                    <div class="col-sm-6">
                        <select class="form-control show-tick" name="state_id" required>
                            <option value="">-- select State--</option>
                            @forelse ($states as $role)
                                <option value="{{$role->id}}">{{$role->name}}</option>
                            @empty
                                <option value="0">No data defined yet.</option>
                            @endforelse
                        </select>
                    </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                        <select class="form-control show-tick" name="city_id" required>
                            <option value="">-- select City--</option>
                            @forelse ($lga as $role)
                                <option value="{{$role->id}}">{{$role->name}}</option>
                            @empty
                                <option value="0">No data defined yet.</option>
                            @endforelse
                        </select>
                    </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                    <div class="col-sm-6">
                        <select class="form-control" name="category_id" required>
                            <option value="">-- select Category--</option>
                            @forelse ($cats as $role)
                                <option value="{{$role->id}}">{{$role->name}}</option>
                            @empty
                                <option value="0">No data defined yet.</option>
                            @endforelse
                        </select>
                    </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <textarea type="text" class="form-control" placeholder="Enter Address"  name="address">Enter Address</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <select class="form-control" name="logistic_id" required>
                                <option value="">-- Logistics --</option>
                                <option value="GoKada">GoKada.</option>
                                <option value="DHL">DHL</option>
                                    
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                        <select class="form-control show-tick" name="delivery_status" required>
                            <option value="">-- Delivery Status --</option>
                            <option value="I only sell and deliver within the country">I only sell and deliver within the country.</option>
                            <option value="I only sell and deliver outside the country">I only sell and deliver outside the country.</option>
                            <option value="I sell and deliver both within and outside the country">I sell and deliver both within and outside the country.</option>
                                
                        </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <label>Upload Photo</label>
                                <input type="file" class="form-control" name="logo"/>
                            </div>
                        </div>
                    </div>
                </div>
            
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">SAVE CHANGES</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">CLOSE</button>
            </div>
            </form>
        </div>
        </div>
    </div>
</div>
