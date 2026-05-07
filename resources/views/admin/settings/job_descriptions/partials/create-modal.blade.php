<!-- This file is optional since we're using the main modal in index.blade.php -->
<!-- But if you want to keep it for reference: -->

<div class="modal fade" id="createPartModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Job description</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createPartForm" method="POST" action="{{ route('admin.job_description.store') }}">
                @csrf
                <div class="modal-body">
                    <!-- Same form fields as in index.blade.php -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
