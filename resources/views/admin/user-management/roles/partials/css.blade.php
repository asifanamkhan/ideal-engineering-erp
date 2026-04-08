<style>
    .permission-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .permission-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .module-title {
        padding: 8px 20px;
        border-bottom: 1px solid #e0e0e0;
        background-color: #f8f9fa;
        border-radius: 8px 8px 0 0;
        font-weight: 600;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .module-title h5 {
        margin: 0;
        font-weight: 600;
        color: #333;
    }

    .select-all-btn {
        font-size: 12px;
        padding: 6px 12px;
        cursor: pointer;
        color: #4E73DF;
        background: none;
        border: 1px solid #4E73DF;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .select-all-btn:hover {
        background-color: #4E73DF;
        color: white;
    }

    .permissions-list {
        padding: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .permission-item {
        min-width: 120px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        padding: 10px;
        gap: 10px;
    }

    .permission-label {
        margin: 0;
        font-size: 14px;
        color: #555;
    }

    /* Switch Toggle Styles */
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
        margin-bottom: 0px !important
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: #4E73DF;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #4E73DF;
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    /* Global Select All Button */
    .global-select-all {
        margin-bottom: 20px;
        text-align: right;
    }

    .global-select-all-btn {
        font-size: 14px;
        padding: 8px 20px;
        cursor: pointer;
        color: #fff;
        background-color: #28a745;
        border: none;
        border-radius: 4px;
        transition: all 0.2s;
        font-weight: 500;
    }

    .global-select-all-btn:hover {
        background-color: #218838;
        transform: translateY(-1px);
    }

    .global-select-all-btn.btn-danger {
        background-color: #dc3545;
    }

    .global-select-all-btn.btn-danger:hover {
        background-color: #c82333;
    }

    @media (max-width: 768px) {
        .permissions-list {
            flex-direction: column;
            gap: 15px;
        }

        .permission-item {
            width: 100%;
        }

        .global-select-all {
            text-align: center;
        }
    }
</style>
