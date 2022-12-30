<div class="modal fade" id="defaultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel"> Add  Coupon</h4>
            </div>
       
            <form  action="{{ route('cms.store', ['page' => 'coupon']) }}" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="row clearfix">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="form-line">
                                <label>Coupon Code</label>
                                <input type="text" class="form-control" id="titleCode" name="coupon_code" required/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="form-line">
                                <label>Coupon Value</label>
                                <input type="number" class="form-control" id="titleCode" name="coupon_value" required/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Coupon Type</label>
                           <select class="form-control show-tick" name="coupon_type" required>
                            {{-- <option value="{{ $product->coupon_type }}">-- Type --</option> --}}
                            <option value="" disabled selected>-- Select Option --</option>
                            <option value="Percentage">Percentage</option>
                            <option value="Fixed Amt">Fixed Amount</option>
                        </select>
                    </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <label>Coupon Start Date</label>
                                <input type="date" class="form-control" id="titleStartDate" name="coupon_start_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <label>Coupon End Date</label>
                                <input type="date" class="form-control" id="titleEndDate" name="coupon_end_date" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <label>Coupon Max Limit</label>
                                <input type="number" class="form-control" id="titleMaxLimit" name="coupon_max_limit" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <label>User Usage Limit</label>
                                <input type="number" class="form-control" id="titleUsageLmt" name="user_usage_limit" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <label>Total Usage Limit</label>
                                <input type="number" class="form-control" id="titleUsage" name="coupon_total_usage" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <label>Minimum Eligibility Amount</label>
                                <input type="number" class="form-control" id="titleElig" name="min_eligibility_amt" required>
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
