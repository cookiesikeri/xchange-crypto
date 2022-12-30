<div class="modal fade" id="defaultModal" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <div class="modal-title"><font color="white">Create Role</font></div>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <form action="{{route('cms.roles.store')}}" method="post">
                                    {{csrf_field()}}
                                    <div class="form-group">
                                        <label for="name" class="control-label">Title</label>
                                        <div class="form-line">
                                        <input type="text" name="title" id="title" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="control-label">Description</label>
                                        <div class="form-line">
                                        <textarea class="form-control" name="description" id="description" name="description" cols="30" rows="10"></textarea>
                                        </div>
                                    </div>
                                    <br>
                                    <button type="submit" class="btn btn-primary btn-sm">Create role</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-grey" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
