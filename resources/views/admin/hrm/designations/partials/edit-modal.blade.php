<!-- This file is optional since we're using the main modal in index.blade.php -->
<!-- But if you want to keep it for reference: -->

<div class="modal fade" id="editDesignationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Designation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editDesignationForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Same form fields as in index.blade.php -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
