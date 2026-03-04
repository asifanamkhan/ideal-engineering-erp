<style>
    .candidate-profile {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        overflow: hidden;
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .profile-header {
        background: #28ACE2;
        color: white;
        padding: 20px 30px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 120px;
    }

    .header-left {
        display: flex;
        align-items: center;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: bold;
        border: 3px solid white;
        margin-right: 20px;
        animation: bounceIn 0.8s ease-out;
    }

    @keyframes bounceIn {
        0% { transform: scale(0.8); opacity: 0; }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); opacity: 1; }
    }

    .header-center {
        flex: 1;
        text-align: center;
    }

    .profile-name {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 5px;
        animation: slideIn 0.6s ease-out 0.2s both;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .profile-position {
        font-size: 18px;
        opacity: 0.9;
        margin-bottom: 8px;
        animation: slideIn 0.6s ease-out 0.3s both;
    }

    .badge-container {
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
        animation: slideIn 0.6s ease-out 0.4s both;
    }

    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-primary {
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
    }

    .badge-success {
        background: #4CAF50;
    }

    .badge-warning {
        background: #FF9800;
    }

    .badge-info {
        background: #2196F3;
    }

    .header-right {
        animation: slideInRight 0.6s ease-out 0.5s both;
    }

    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .btn-view-resume {
        background: white;
        color: #28ACE2;
        border: none;
        padding: 12px 24px;
        border-radius: 25px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-view-resume:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        background: #f8f9fa;
    }

    .btn-view-resume i {
        font-size: 16px;
    }

    .profile-body {
        padding: 30px;
        animation: fadeInUp 0.6s ease-out 0.6s both;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #28ACE2;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
        position: relative;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 50px;
        height: 2px;
        background: #28ACE2;
    }

    .info-item {
        margin-bottom: 15px;
        display: flex;
        padding: 10px 0;
        border-bottom: 1px solid #f5f5f5;
        transition: all 0.3s ease;
    }

    .info-item:hover {
        background: #f9f9f9;
        padding-left: 10px;
        border-radius: 5px;
    }

    .info-label {
        font-weight: 600;
        width: 180px;
        color: #555;
        flex-shrink: 0;
    }

    .info-value {
        flex: 1;
        color: #333;
    }

    .social-links {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .social-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: #f8f9fa;
        border-radius: 20px;
        color: #28ACE2;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }

    .social-link:hover {
        background: #28ACE2;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 172, 226, 0.3);
        text-decoration: none;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease-out;
    }

    .modal-content {
        background: white;
        border-radius: 15px;
        width: 95%;
        height: 95%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        animation: scaleIn 0.3s ease-out;
    }

    @keyframes scaleIn {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    .modal-header {
        padding: 20px;
        background: #28ACE2;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        font-weight: 600;
        font-size: 18px;
    }

    .close-modal {
        background: rgba(255,255,255,0.2);
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-size: 20px;
        cursor: pointer;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .close-modal:hover {
        background: rgba(255,255,255,0.3);
        transform: rotate(90deg);
    }

    .modal-body {
        flex: 1;
        padding: 0;
        overflow: hidden;
    }

    .pdf-frame {
        width: 100%;
        height: 100%;
        border: none;
    }

    .experience-badge {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .salary-badge {
        background: linear-gradient(135deg, #4CAF50, #45a049);
        color: white;
    }

    .no-resume {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        flex-direction: column;
        color: #666;
    }

    .no-resume i {
        font-size: 48px;
        margin-bottom: 15px;
        color: #ddd;
    }

    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
            gap: 15px;
            padding: 20px;
        }

        .header-left {
            flex-direction: column;
        }

        .profile-avatar {
            margin-right: 0;
            margin-bottom: 15px;
        }

        .info-item {
            flex-direction: column;
        }

        .info-label {
            width: 100%;
            margin-bottom: 5px;
        }
    }
</style>
