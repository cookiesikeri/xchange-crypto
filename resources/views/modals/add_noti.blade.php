<div class="modal fade" id="defaultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel"> Add Notification</h4>
            </div>
            <form  action="{{route('cms.submit.notification')}}" method="POST" enctype="multipart/form-data">
                {{csrf_field()}}
                @method('POST')
            <div class="modal-body">
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <select class="form-control show-tick" name="type">
                                <option value="">-- Please select --</option>
                                <option value="all">All Users</option>
                                <option value="customerservice">Customer Service</option>
                                <option value="accountant">Accountants</option>
                                <option value="data">Data Entry Officers</option>
                                <option value="marketing">Marketing</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12">
                        <div class="form-line">
                            <input type="text" name="title" class="form-control" placeholder="Enter  Title.">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="message">Description</label>
                        <div class="form-line">
                        <textarea name="description" class="form-control" rows="3" placeholder="Enter Your Message" required></textarea>
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
