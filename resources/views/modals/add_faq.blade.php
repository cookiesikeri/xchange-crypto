    <!-- BASIC MODAL -->
    <div id="modaldemo" class="modal fade">
        <div class="modal-dialog modal-dialog-vertical-center" role="document">
          <div class="modal-content bd-0 tx-14">
            <div class="modal-header pd-y-20 pd-x-25">
              <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold">Add FAQ</h6>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body pd-25">
            <form id="faq" action="{{ route('cms.store', ['page' => 'faqs']) }}" method="POST" enctype="multipart/form-data">
              {{csrf_field()}}
              <input type="hidden" name="_method" value="POST">
                      <div class="row">
                      <div class="col-md-12">
                              <div class="form-group">
                                  <label for="">Title</label>
                                  <input type="text" name="title" class="form-control" required>
                              </div>
                          </div>
                          <div class="col-md-12">
                              <div class="form-group">
                                  <label for="">Description</label>
                                  <textarea type="text" name="body" id="summernote" class="form-control" required></textarea>
                              </div>
                          </div>
                      </div>


            <div class="modal-footer">
              <button type="submit" class="btn btn-primary pd-x-20">Save</button>
              <button type="button" class="btn btn-secondary pd-x-20" data-dismiss="modal">Close</button>
            </div>
            </form>
          </div>
          </div>
        </div><!-- modal-dialog -->

      </div><!-- modal -->
