# Estimate Item Field Name Fix - unit_price를 rate로 통일

## 문제점

Estimate 생성 시 다음 에러가 발생:

```
Setting unknown property: app\models\EstimateItem::unit_price
```

## 원인

`EstimateController`에서 `EstimateItem` 객체에 `unit_price` 속성을 설정하려고 했지만, 실제 `EstimateItem` 모델의 필드명은 `rate`입니다.

## 해결방법

`EstimateController`에서 `unit_price` 대신 올바른 필드명 `rate`를 사용하도록 다음 메서드들을 수정:

1. **actionCreate()** - 새 estimate item 생성 시
2. **actionUpdate()** - estimate item 업데이트 시
3. **actionDuplicate()** - estimate item 복사 시
4. **actionConvertToInvoice()** - estimate item을 invoice item으로 변환 시

하위 호환성을 위해 폼 데이터에서 `rate`와 `unit_price` 모두 확인:

```php
$item->rate = $itemData['rate'] ?? ($itemData['unit_price'] ?? 0);
```

## 수정된 파일

- `controllers/EstimateController.php`

## 확인사항

수정 후 다음 기능들이 에러 없이 작동해야 함:

1. 새 estimate 생성 (아이템 포함)
2. 기존 estimate 수정
3. estimate 복제
4. estimate를 invoice로 변환

## 필드명 통일 현황

- ✅ `EstimateItem` 모델: `rate` 필드 사용
- ✅ `InvoiceItem` 모델: `rate` 필드 사용
- ✅ `EstimateController`: `rate` 사용 (unit_price는 fallback으로만)
- ✅ `InvoiceController`: `rate` 사용
- ✅ 뷰 파일들: `rate` 사용
- ✅ JavaScript 파일들: `rate` 사용
