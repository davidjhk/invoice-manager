#!/bin/bash
# Admin Settings 테이블 확인 스크립트

echo "=== Admin Settings 테이블 확인 ==="

# 현재 디렉토리 확인
if [ -d "/opt/bitnami/apps/jdosa/invoice-manager" ]; then
    cd /opt/bitnami/apps/jdosa/invoice-manager
elif [ -d "/opt/bitnami/apps/invoice-manager" ]; then
    cd /opt/bitnami/apps/invoice-manager
fi

echo "작업 디렉토리: $(pwd)"

# 1단계: 테이블 존재 확인
echo "1단계: 테이블 존재 확인 중..."
TABLE_EXISTS=$(mysql -u bn_wordpress -pd6ab501583 -D bitnami_wordpress -e "SHOW TABLES LIKE 'jdosa_admin_settings';" 2>/dev/null | wc -l)

if [ "$TABLE_EXISTS" -gt 0 ]; then
    echo "✅ jdosa_admin_settings 테이블이 존재합니다!"
    
    # 2단계: 테이블 구조 확인
    echo "2단계: 테이블 구조 확인 중..."
    mysql -u bn_wordpress -pd6ab501583 -D bitnami_wordpress -e "DESCRIBE jdosa_admin_settings;" 2>/dev/null
    
    # 3단계: 데이터 확인
    echo "3단계: 설정 데이터 확인 중..."
    mysql -u bn_wordpress -pd6ab501583 -D bitnami_wordpress -e "SELECT setting_key, setting_value, description FROM jdosa_admin_settings ORDER BY setting_key;" 2>/dev/null
    
    # 4단계: 중요 설정 확인
    echo "4단계: 중요 설정 확인 중..."
    ALLOW_SIGNUP=$(mysql -u bn_wordpress -pd6ab501583 -D bitnami_wordpress -e "SELECT setting_value FROM jdosa_admin_settings WHERE setting_key='allow_signup';" 2>/dev/null | tail -n 1)
    
    if [ "$ALLOW_SIGNUP" = "0" ]; then
        echo "✅ 사용자 등록이 올바르게 비활성화되어 있습니다."
    else
        echo "⚠️  사용자 등록이 활성화되어 있습니다. 보안상 비활성화를 권장합니다."
    fi
    
    echo ""
    echo "🎉 Admin Settings 시스템이 정상적으로 작동합니다!"
    echo "이제 다음 URL에 접근할 수 있습니다:"
    echo "• Admin Dashboard: https://invoice.jdosa.com/admin"
    echo "• Admin Settings: https://invoice.jdosa.com/admin/settings"
    echo "• Create User: https://invoice.jdosa.com/admin/create-user"
    
else
    echo "❌ jdosa_admin_settings 테이블이 존재하지 않습니다."
    echo "마이그레이션이 실행되었지만 테이블이 생성되지 않았을 수 있습니다."
    echo ""
    echo "수동으로 테이블을 생성하겠습니다..."
    
    # 수동 테이블 생성
    mysql -u bn_wordpress -pd6ab501583 -D bitnami_wordpress << 'EOF'
CREATE TABLE IF NOT EXISTS `jdosa_admin_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) NOT NULL,
    `setting_value` text,
    `description` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `jdosa_admin_settings` (`setting_key`, `setting_value`, `description`) VALUES
('allow_signup', '0', 'Allow new user registration (1 = enabled, 0 = disabled)'),
('max_users', '100', 'Maximum number of users allowed'),
('site_maintenance', '0', 'Site maintenance mode (1 = enabled, 0 = disabled)'),
('password_min_length', '6', 'Minimum password length requirement'),
('session_timeout', '3600', 'Session timeout in seconds'),
('email_notifications', '1', 'Enable email notifications'),
('backup_enabled', '1', 'Enable automatic backups'),
('max_companies_per_user', '5', 'Maximum companies per user');
EOF
    
    if [ $? -eq 0 ]; then
        echo "✅ 테이블 수동 생성 완료!"
        echo "다시 확인 중..."
        
        # 다시 확인
        mysql -u bn_wordpress -pd6ab501583 -D bitnami_wordpress -e "SELECT setting_key, setting_value FROM jdosa_admin_settings;" 2>/dev/null
        
        echo ""
        echo "🎉 Admin Settings 시스템 설정 완료!"
        echo "• Admin Dashboard: https://invoice.jdosa.com/admin"
        echo "• Admin Settings: https://invoice.jdosa.com/admin/settings"
    else
        echo "❌ 테이블 생성 실패"
        echo "수동으로 MySQL에 접속하여 테이블을 생성해야 합니다."
    fi
fi

echo ""
echo "=== 완료 ==="