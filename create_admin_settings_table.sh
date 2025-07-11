#!/bin/bash
# Admin Settings 테이블 생성 스크립트

echo "=== Admin Settings 테이블 생성 ==="

# 현재 디렉토리 확인
if [ -d "/opt/bitnami/apps/jdosa/invoice-manager" ]; then
    cd /opt/bitnami/apps/jdosa/invoice-manager
elif [ -d "/opt/bitnami/apps/invoice-manager" ]; then
    cd /opt/bitnami/apps/invoice-manager
else
    echo "프로젝트 디렉토리를 찾을 수 없습니다."
    echo "현재 디렉토리에서 실행합니다: $(pwd)"
fi

echo "작업 디렉토리: $(pwd)"

# 1단계: 마이그레이션 상태 확인
echo "1단계: 마이그레이션 상태 확인 중..."
./yii migrate/history 5 2>/dev/null || echo "마이그레이션 히스토리를 확인할 수 없습니다."

# 2단계: 테이블 존재 확인
echo "2단계: 테이블 존재 확인 중..."
mysql -u bn_wordpress -pd6ab501583 -D bitnami_wordpress -e "SHOW TABLES LIKE 'jdosa_admin_settings';" 2>/dev/null || echo "테이블 확인 실패"

# 3단계: 마이그레이션 실행
echo "3단계: 마이그레이션 실행 중..."
./yii migrate --interactive=0 || {
    echo "❌ 마이그레이션 실패"
    echo "수동으로 테이블을 생성합니다..."
    
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
        echo "✅ 테이블 수동 생성 완료"
    else
        echo "❌ 테이블 생성 실패"
        exit 1
    fi
}

# 4단계: 테이블 생성 확인
echo "4단계: 테이블 생성 확인 중..."
mysql -u bn_wordpress -pd6ab501583 -D bitnami_wordpress -e "DESCRIBE jdosa_admin_settings;" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ jdosa_admin_settings 테이블이 성공적으로 생성되었습니다!"
    
    # 데이터 확인
    echo "5단계: 초기 데이터 확인 중..."
    mysql -u bn_wordpress -pd6ab501583 -D bitnami_wordpress -e "SELECT setting_key, setting_value, description FROM jdosa_admin_settings LIMIT 5;"
    
    echo ""
    echo "🎉 Admin Settings 테이블 생성 완료!"
    echo "이제 https://invoice.jdosa.com/admin/settings 에 접근할 수 있습니다."
else
    echo "❌ 테이블 생성 실패"
    exit 1
fi

echo ""
echo "=== 완료 ==="