<div class="modal fade" id="createAdminModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel">Create new admin account</h4>
            </div>
            <form  action="{{route('cms.users.store')}}" method="POST" >
                {{csrf_field()}}
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for=""> Name <span class="tx-danger">*</span></label>
                            <div class="form-line">
                            <input type="text" name="name" id="name" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">E-mail <span class="tx-danger">*</span></label>
                            <div class="form-line">
                            <input type="email" name="email" id="email" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Password <span class="tx-danger">*</span></label>
                            <div class="form-line">
                            <input type="password" name="password" id="password" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Confirm Password <span class="tx-danger">*</span></label>
                            <div class="form-line">
                            <input type="password" name="password_confirmation" id="password-confirm" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <select class="form-control show-tick" name="role_id" required>
                            <option value="">-- select Role--</option>

                            @forelse ($roles as $role)
                                <option value="{{$role->id}}">{{$role->title}}</option>
                            @empty
                                <option value="0">No roles defined yet.</option>
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
