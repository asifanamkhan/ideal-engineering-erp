<style>
    .job-profile {
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
        background: linear-gradient(135deg, #28ACE2 0%, #1E88E5 100%);
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

    .job-icon {
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

    .job-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 5px;
        animation: slideIn 0.6s ease-out 0.2s both;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .job-meta {
        font-size: 16px;
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
        margin-top: 10px;
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

    .badge-danger {
        background: #FF5252;
    }

    .badge-warning {
        background: #FF9800;
    }

    .vacancies-badge {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .header-right {
        animation: slideInRight 0.6s ease-out 0.5s both;
    }

    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .status-badge {
        background: white;
        color: #28ACE2;
        border: none;
        padding: 12px 24px;
        border-radius: 25px;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 8px;
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
        transform: translateX(5px);
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

    .description-section {
        margin-bottom: 30px;
    }

    .description-content {
        line-height: 1.8;
        color: #495057;
        font-size: 15px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #28ACE2;
    }

    .exam-badges {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        justify-content: center;
        padding: 20px;
    }

    .exam-badge {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
    }

    .exam-badge:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .deadline-card {
        background: linear-gradient(135deg, #FF6B6B, #EE5A24);
        color: white;
        padding: 25px;
        border-radius: 15px;
        text-align: center;
        margin-bottom: 30px;
        box-shadow: 0 5px 25px rgba(255, 107, 107, 0.3);
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }

    .deadline-time {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .deadline-label {
        font-size: 14px;
        opacity: 0.9;
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

        .job-icon {
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
