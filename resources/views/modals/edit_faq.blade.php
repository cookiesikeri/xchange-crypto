<div class="modal fade" id="defaultModal2" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
      <div class="modal-content">
          <div class="modal-header">
              <h4 class="modal-title" id="defaultModalLabel"> Edit FAQ</h4>
          </div>
          <form  action="" method="POST" enctype="multipart/form-data" id="faq">
              {{csrf_field()}}
              <input type="hidden" name="_method" value="PUT">
          <div class="modal-body">
              <div class="row clearfix">
                  <div class="col-sm-6">
                      <div class="form-group">
                          <div class="form-line">
                              <input type="text" class="form-control" id="titleEdit" name="name"/>
                          </div>
                      </div>
                  </div>
              </div>
              <div class="row clearfix">
                  <div class="col-sm-6">
                      <div class="form-group">
                          <div class="form-line">
                              <textarea type="text" class="form-control" id="titleCode" name="body"></textarea>
                          </div>
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
