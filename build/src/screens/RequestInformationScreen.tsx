import React, { useState } from 'react';
import {
  Car,
  Phone,
  HelpCircle,
  CheckCircle2,
  AlertCircle,
  MapPin,
  Clock,
  Sparkles,
  ChevronDown,
  ChevronUp,
} from 'lucide-react';
import { useApp } from '../context/AppContext';
import { getTranslation } from '../data/translations';
import { VehicleTypeSelector } from '../components/VehicleTypeSelector';
import { PhotoUploadCard } from '../components/PhotoUploadCard';
import { PrimaryCTAButton } from '../components/PrimaryCTAButton';
import { SERVICES_DATA } from '../data/mockData';
import {
  createOnsiteLookupCode,
  formatPhoneInput,
  isCallablePhone,
  isOnsiteLookupCode,
  isKoreanPhone,
  normalizePhone,
} from '../lib/dispatchApi';

export const RequestInformationScreen: React.FC = () => {
  const {
    lang,
    selectedService,
    serviceCondition,
    setServiceCondition,
    selectedVehicleType,
    setSelectedVehicleType,
    vehicleModelName,
    setVehicleModelName,
    plateNumber,
    setPlateNumber,
    contactPhone,
    setContactPhone,
    messengerType,
    setMessengerType,
    messengerId,
    setMessengerId,
    contactMode,
    setContactMode,
    viewPin,
    setViewPin,
    locationAddress,
    landmark,
    setLandmark,
    submitDispatchRequest,
    navigateTo,
    showToast,
  } = useApp();
  const t = getTranslation(lang);

  const [showVehicleDetails, setShowVehicleDetails] = useState(false);
  const [phoneError, setPhoneError] = useState(false);
  const [pinError, setPinError] = useState(false);

  const service = selectedService || SERVICES_DATA[0];

  // Service condition options per service type requested by user
  const getConditionOptions = () => {
    switch (service.id) {
      case 'battery':
        return [
          { ko: '시동이 전혀 걸리지 않아요', en: 'Engine will not crank at all' },
          { ko: '배터리가 방전된 것 같아요', en: 'Battery seems fully drained' },
          { ko: '잘 모르겠어요', en: 'Not sure / Need check' },
        ];
      case 'tire':
        return [
          { ko: '타이어 펑크', en: 'Flat / Punctured tire' },
          { ko: '타이어 공기 부족', en: 'Low tire pressure' },
          { ko: '스페어 타이어 교체', en: 'Spare tire replacement' },
          { ko: '잘 모르겠어요', en: 'Not sure / Need check' },
        ];
      case 'towing':
        return [
          { ko: '차량 고장', en: 'Mechanical breakdown' },
          { ko: '교통사고', en: 'Traffic collision' },
          { ko: '시동 불가', en: 'Engine won’t start' },
          { ko: '기타', en: 'Other towing reason' },
        ];
      case 'lockout':
        return [
          { ko: '스마트키 차내에 두고 내림', en: 'Key locked inside vehicle' },
          { ko: '차 열쇠 분실', en: 'Car key lost' },
          { ko: '트렁크 잠김', en: 'Trunk locked' },
          { ko: '기타', en: 'Other lockout issue' },
        ];
      case 'sos':
      default:
        return [
          { ko: '원인 미상 시동 꺼짐', en: 'Sudden engine stall' },
          { ko: '비상 주유 필요', en: 'Emergency fuel empty' },
          { ko: '침수 / 배수로 낙하', en: 'Flooded / Stuck in ditch' },
          { ko: '기타 긴급 점검', en: 'General urgent rescue' },
        ];
    }
  };

  const conditionOptions = getConditionOptions();

  const handlePhoneChange = (val: string) => {
    const digits = normalizePhone(val);
    let country: 'ph' | 'kr' = contactMode === 'kr' ? 'kr' : 'ph';
    if (/^010\d{8}$/.test(digits)) country = 'kr';
    else if (/^09\d{9}$/.test(digits)) country = 'ph';
    if (country !== contactMode) setContactMode(country);
    setContactPhone(formatPhoneInput(digits, country));
    setPhoneError(false);
  };

  const selectCountry = (country: 'ph' | 'kr') => {
    setContactMode(country);
    setContactPhone(formatPhoneInput(contactPhone, country));
    setPhoneError(false);
  };

  const enableOnsiteWait = () => {
    setContactMode('onsite');
    if (!isOnsiteLookupCode(contactPhone)) {
      setContactPhone(createOnsiteLookupCode());
    }
    setShowVehicleDetails(true);
    setPhoneError(false);
  };

  const isPhoneValid =
    contactMode === 'onsite' ? isOnsiteLookupCode(contactPhone) : isCallablePhone(contactPhone);

  const handleSubmit = () => {
    if (contactMode === 'onsite') {
      if (landmark.trim().length < 4 || plateNumber.trim().length < 4) {
        setPhoneError(true);
        setShowVehicleDetails(true);
        showToast(
          lang === 'ko'
            ? '⚠️ 현장 대기는 위치 설명과 차량 번호판을 입력해 주세요.'
            : '⚠️ On-site wait needs a landmark and plate number.'
        );
        return;
      }
    } else if (!isCallablePhone(contactPhone) || (contactMode === 'kr' && !isKoreanPhone(contactPhone)) || (contactMode === 'ph' && isKoreanPhone(contactPhone))) {
      setPhoneError(true);
      showToast(
        lang === 'ko'
          ? contactMode === 'kr'
            ? '⚠️ 한국 번호는 010으로 시작하는 11자리를 입력해 주세요.'
            : '⚠️ 필리핀 번호는 09로 시작하는 11자리를 입력해 주세요.'
          : contactMode === 'kr'
            ? '⚠️ Enter an 11-digit Korean number starting with 010.'
            : '⚠️ Enter an 11-digit Philippine number starting with 09.'
      );
      return;
    }
    if (!/^\d{4}$/.test(viewPin)) {
      setPinError(true);
      showToast(
        lang === 'ko'
          ? '⚠️ 조회·보안 비밀번호 4자리를 입력해 주세요 (기본 1234)'
          : '⚠️ Enter a 4-digit inquiry PIN (default 1234)'
      );
      return;
    }
    navigateTo('request_confirm');
  };

  return (
    <div id="screen-request-info" className="pb-32 p-4 animate-fade-in text-white">
      {/* SCREEN TITLE */}
      <div className="mb-3.5">
        <h1 className="text-2xl font-black tracking-tight text-white">
          {lang === 'ko' ? '출동 신청 정보' : 'Request Information'}
        </h1>
        <p className="text-xs text-[#A7A7A7] mt-1">
          {lang === 'ko'
            ? '빠른 출동을 위해 필수 정보(연락처)만 입력하시면 즉시 기사가 배정됩니다.'
            : 'Minimal info required for 20-30s express dispatch without sign-up.'}
        </p>
      </div>

      {/* Confirmed Location & Service Pill Banner */}
      <div className="bg-[#141416] border border-[#2c2c2e] rounded-2xl p-3 mb-4 flex items-center justify-between">
        <div className="flex items-center gap-2 overflow-hidden">
          <div className="w-7 h-7 rounded-lg bg-[#FFC400]/20 flex items-center justify-center shrink-0">
            <MapPin className="w-3.5 h-3.5 text-[#FFC400]" />
          </div>
          <div className="truncate">
            <p className="text-xs font-bold text-white truncate">
              {locationAddress}
            </p>
            {landmark && (
              <p className="text-[10px] text-[#A7A7A7] truncate">
                {landmark}
              </p>
            )}
          </div>
        </div>
        <span className="px-2.5 py-1 rounded-full text-[10px] font-black bg-[#FFC400] text-black shrink-0 ml-2">
          {lang === 'ko' ? service.nameKo : service.nameEn}
        </span>
      </div>

      {/* 1. SERVICE DETAIL: Condition Question & Options */}
      <div className="bg-[#1C1C1E] border border-[#2c2c2e] rounded-2xl p-4 mb-4 shadow-lg">
        <div className="flex items-center justify-between mb-2">
          <div className="flex items-center gap-2">
            <span className="px-2 py-0.5 rounded-full text-[10px] font-black bg-[#FFC400]/20 text-[#FFC400] uppercase">
              {lang === 'ko' ? service.nameKo : service.nameEn}
            </span>
            <label className="text-xs font-bold text-white">
              {t.conditionQuestionTitle}
            </label>
          </div>
        </div>

        <div className="space-y-2 mt-2">
          {conditionOptions.map((opt) => {
            const label = lang === 'ko' ? opt.ko : opt.en;
            const isSelected = serviceCondition === label;
            return (
              <button
                key={opt.ko}
                type="button"
                onClick={() => setServiceCondition(label)}
                className={`w-full py-2.5 px-3.5 rounded-xl border text-left flex items-center justify-between transition-all active:scale-[0.99] ${
                  isSelected
                    ? 'bg-[#FFC400]/15 border-[#FFC400] text-white'
                    : 'bg-[#141416] border-[#292929] text-[#D1D5DB] hover:border-[#38383a]'
                }`}
              >
                <div className="flex items-center gap-2.5">
                  <div
                    className={`w-4 h-4 rounded-full border flex items-center justify-center ${
                      isSelected
                        ? 'border-[#FFC400] bg-[#FFC400]'
                        : 'border-[#555] bg-transparent'
                    }`}
                  >
                    {isSelected && <div className="w-1.5 h-1.5 rounded-full bg-black" />}
                  </div>
                  <span className="text-xs font-medium">{label}</span>
                </div>
                {isSelected && <CheckCircle2 className="w-4 h-4 text-[#FFC400]" />}
              </button>
            );
          })}
        </div>
      </div>

      {/* 2. VEHICLE TYPE: Icon Button Grid */}
      <div className="bg-[#1C1C1E] border border-[#2c2c2e] rounded-2xl p-4 mb-4 shadow-lg">
        <div className="flex items-center justify-between mb-2.5">
          <label className="text-xs font-bold text-white flex items-center gap-1.5">
            <Car className="w-4 h-4 text-[#FFC400]" />
            <span>{t.vehicleTypeHeading}</span>
          </label>
          <span className="text-[10px] text-[#A7A7A7]">
            {lang === 'ko' ? '아이콘 선택' : 'Tap to select'}
          </span>
        </div>

        <VehicleTypeSelector
          selectedType={selectedVehicleType}
          onSelect={setSelectedVehicleType}
        />

        {/* Optional Vehicle Details Accordion (선택사항) */}
        <div className="mt-3 pt-3 border-t border-[#292929]">
          <button
            type="button"
            onClick={() => setShowVehicleDetails(!showVehicleDetails)}
            className="w-full flex items-center justify-between text-xs text-[#A7A7A7] hover:text-white py-1"
          >
            <span className="font-medium">
              {lang === 'ko'
                ? '+ 차량 브랜드 / 번호판 입력 (선택사항)'
                : '+ Add vehicle model & plate (Optional)'}
            </span>
            {showVehicleDetails ? (
              <ChevronUp className="w-3.5 h-3.5" />
            ) : (
              <ChevronDown className="w-3.5 h-3.5" />
            )}
          </button>

          {showVehicleDetails && (
            <div className="mt-2.5 space-y-2.5 animate-fade-in">
              <div>
                <label className="text-[11px] font-semibold text-[#A7A7A7] mb-1 block">
                  {lang === 'ko' ? '차량 모델 / 색상 (선택)' : 'Vehicle Model & Color (Optional)'}
                </label>
                <input
                  type="text"
                  value={vehicleModelName}
                  onChange={(e) => setVehicleModelName(e.target.value)}
                  placeholder={lang === 'ko' ? '예: 포튜너 흰색 / 아반떼 검정' : 'e.g. Fortuner White / Vios Black'}
                  className="w-full bg-[#111111] border border-[#3a3a3c] rounded-xl px-3 py-2 text-xs text-white placeholder:text-[#666] focus:border-[#FFC400] outline-none"
                />
              </div>

              <div>
                <label className="text-[11px] font-semibold text-[#A7A7A7] mb-1 block">
                  {lang === 'ko' ? '차량 번호판 (선택)' : 'Plate Number (Optional)'}
                </label>
                <input
                  type="text"
                  value={plateNumber}
                  onChange={(e) => setPlateNumber(e.target.value)}
                  placeholder="예: GAF 3982"
                  className="w-full bg-[#111111] border border-[#3a3a3c] rounded-xl px-3 py-2 text-xs text-white uppercase placeholder:text-[#666] focus:border-[#FFC400] outline-none"
                />
              </div>
            </div>
          )}
        </div>
      </div>

      {/* 3. CONTACT: PH, KR roaming, or on-site wait */}
      <div
        className={`bg-[#1C1C1E] border rounded-2xl p-4 mb-4 shadow-lg transition-colors ${
          phoneError ? 'border-[#E53935]' : 'border-[#2c2c2e]'
        }`}
      >
        <div className="flex items-center justify-between mb-1.5">
          <label
            htmlFor="contact-phone-input"
            className="text-xs font-bold text-white flex items-center gap-1.5"
          >
            <Phone className="w-4 h-4 text-[#22C55E]" />
            <span>{t.contactPhoneHeading}</span>
            <span className="text-[#FFC400] font-black">*</span>
          </label>
          <span className="px-2 py-0.5 rounded-full text-[10px] font-black bg-[#22C55E]/20 text-[#22C55E]">
            {lang === 'ko' ? '필수 입력' : 'Required'}
          </span>
        </div>

        <div className="grid grid-cols-3 gap-1.5 mt-2">
          {(
            [
              ['ph', '🇵🇭 +63'],
              ['kr', '🇰🇷 +82'],
              ['onsite', lang === 'ko' ? '번호 없음' : 'No number'],
            ] as const
          ).map(([id, label]) => (
            <button
              key={id}
              type="button"
              onClick={() => (id === 'onsite' ? enableOnsiteWait() : selectCountry(id))}
              className={`min-h-[36px] rounded-xl border text-[11px] font-black ${
                contactMode === id
                  ? 'bg-[#FFC400] text-black border-[#FFC400]'
                  : 'bg-[#111111] text-[#D1D5DB] border-[#3a3a3c]'
              }`}
            >
              {label}
            </button>
          ))}
        </div>

        {contactMode === 'onsite' ? (
          <div className="mt-3 rounded-xl border border-[#FFC400]/40 bg-[#FFC400]/10 px-3 py-2.5">
            <p className="text-xs font-black text-[#FFC400]">
              {lang === 'ko' ? '현장 대기' : 'Wait on site'}
            </p>
            <p className="text-[11px] text-[#E5E7EB] mt-1 leading-relaxed">
              {lang === 'ko'
                ? '기사님이 전화하지 않고 위치와 번호판으로 찾습니다. 아래 조회 코드와 비밀번호로 나중에 접수를 다시 볼 수 있습니다.'
                : 'The driver finds the car by landmark and plate, without calling. Keep this code and PIN to look the request up later.'}
            </p>
            <p className="mt-2 font-mono text-sm font-black text-white">{contactPhone}</p>
          </div>
        ) : (
          <div className="relative flex items-center mt-2">
            <div className="absolute left-3.5 flex items-center gap-1.5 text-xs font-bold text-white pointer-events-none">
              <span className="text-sm">{contactMode === 'kr' ? '🇰🇷' : '🇵🇭'}</span>
              <span className="text-[#A7A7A7] font-mono">{contactMode === 'kr' ? '+82' : '+63'}</span>
            </div>
            <input
              id="contact-phone-input"
              type="tel"
              name="tel"
              autoComplete="tel"
              inputMode="numeric"
              value={contactPhone}
              onChange={(e) => handlePhoneChange(e.target.value)}
              placeholder={contactMode === 'kr' ? '010-XXXX-XXXX' : '09XX XXX XXXX'}
              className={`w-full bg-[#111111] border rounded-xl pl-20 pr-10 py-3 text-sm font-bold font-mono tracking-wider text-white placeholder:text-[#555] focus:border-[#FFC400] focus:ring-1 focus:ring-[#FFC400] outline-none transition-all ${
                phoneError ? 'border-[#E53935]' : 'border-[#3a3a3c]'
              }`}
            />
            {isPhoneValid && (
              <div className="absolute right-3 text-[#22C55E]">
                <CheckCircle2 className="w-5 h-5" />
              </div>
            )}
          </div>
        )}

        <p className="text-[11px] text-[#A7A7A7] mt-2.5 leading-relaxed flex items-start gap-1.5">
          <Sparkles className="w-3.5 h-3.5 text-[#FFC400] shrink-0 mt-0.5" />
          <span>
            {contactMode === 'onsite'
              ? lang === 'ko'
                ? '본인 번호가 없으면 동행자, 호텔, 렌터카 번호가 더 안전합니다. 메신저 아이디는 이 기기에 저장되어 다음에 미리 채워집니다.'
                : 'A companion, hotel, or rental number is safer. The messenger ID is saved on this device and can be edited next time.'
              : lang === 'ko'
                ? `${t.phoneHelper} 이 기기에 저장되어 다음 접수 때 미리 채워집니다. 틀리면 고쳐 주세요.`
                : `${t.phoneHelper} Saved on this device and filled in next time. Edit it if it is wrong.`}
          </span>
        </p>
        {contactMode === 'onsite' && (
          <div className="mt-3 space-y-2.5">
            <div>
              <label className="text-[11px] font-semibold text-[#A7A7A7] mb-1 block">
                {lang === 'ko' ? '위치 설명 *' : 'Landmark *'}
              </label>
              <input
                type="text"
                value={landmark}
                onChange={(e) => setLandmark(e.target.value)}
                placeholder={t.landmarkPlaceholder}
                className="w-full bg-[#111111] border border-[#3a3a3c] rounded-xl px-3 py-2 text-xs text-white placeholder:text-[#666] focus:border-[#FFC400] outline-none"
              />
            </div>
            <div>
              <label className="text-[11px] font-semibold text-[#A7A7A7] mb-1 block">
                {lang === 'ko' ? '차량 번호판 *' : 'Plate number *'}
              </label>
              <input
                type="text"
                value={plateNumber}
                onChange={(e) => setPlateNumber(e.target.value)}
                placeholder="예: GAF 3982"
                className="w-full bg-[#111111] border border-[#3a3a3c] rounded-xl px-3 py-2 text-xs text-white uppercase placeholder:text-[#666] focus:border-[#FFC400] outline-none"
              />
            </div>
            <div>
              <label className="text-[11px] font-semibold text-[#A7A7A7] mb-1 block">
                {lang === 'ko' ? '메신저 (선택)' : 'Messenger (optional)'}
              </label>
              <div className="grid grid-cols-2 gap-1.5">
                {(
                  [
                    ['kakao', '카카오톡'],
                    ['facebook', lang === 'ko' ? '페이스북' : 'Facebook'],
                  ] as const
                ).map(([id, label]) => (
                  <button
                    key={id}
                    type="button"
                    onClick={() => setMessengerType(messengerType === id ? '' : id)}
                    className={`min-h-[36px] rounded-xl border text-[11px] font-black ${
                      messengerType === id
                        ? 'bg-[#FFC400] text-black border-[#FFC400]'
                        : 'bg-[#111111] text-[#D1D5DB] border-[#3a3a3c]'
                    }`}
                  >
                    {label}
                  </button>
                ))}
              </div>
              <input
                id="messenger-id-input"
                type="text"
                value={messengerId}
                maxLength={80}
                onChange={(e) => setMessengerId(e.target.value)}
                placeholder={
                  messengerType === 'facebook'
                    ? lang === 'ko'
                      ? '페이스북 사용자명'
                      : 'Facebook username'
                    : lang === 'ko'
                      ? '카카오톡 아이디'
                      : 'KakaoTalk ID'
                }
                className="mt-2 w-full bg-[#111111] border border-[#3a3a3c] rounded-xl px-3 py-2 text-xs text-white placeholder:text-[#666] focus:border-[#FFC400] outline-none"
              />
            </div>
          </div>
        )}
        {isPhoneValid && (
          <p className="text-[11px] text-[#22C55E] mt-1.5 font-bold">
            {lang === 'ko'
              ? `접수번호 예: #CR날짜-${contactPhone.replace(/\D/g, '').slice(-4)} (CR=Cebu Rescue, 뒤는 이 번호 뒷 4자리)`
              : `Receipt e.g. #CR+date-${contactPhone.replace(/\D/g, '').slice(-4)} (CR=Cebu Rescue)`}
          </p>
        )}

        {/* Inquiry / security PIN (below contact) */}
        <div
          className={`mt-4 pt-4 border-t border-[#292929] ${
            pinError ? 'opacity-100' : ''
          }`}
        >
          <div className="flex items-center justify-between mb-1.5">
            <label
              htmlFor="view-pin-input"
              className="text-xs font-bold text-white"
            >
              {lang === 'ko'
                ? '조회 및 보안 비밀번호 4자리'
                : 'Inquiry & security PIN (4 digits)'}
            </label>
            <span className="text-[10px] font-bold text-[#22C55E]">
              {lang === 'ko' ? '기본 1234' : 'Default 1234'}
            </span>
          </div>
          <input
            id="view-pin-input"
            type="password"
            inputMode="numeric"
            maxLength={4}
            value={viewPin}
            onChange={(e) => {
              setViewPin(e.target.value);
              setPinError(false);
            }}
            placeholder="1234"
            className={`w-full h-12 rounded-xl bg-[#111111] border px-3 text-center text-lg font-black tracking-[0.4em] text-white outline-none ${
              pinError ? 'border-[#E53935]' : 'border-[#3a3a3c] focus:border-[#22C55E]'
            }`}
          />
          <p className="text-[11px] text-[#A7A7A7] mt-2 leading-relaxed">
            {lang === 'ko'
              ? '다른 사람 폰으로 신청해도, 내 폰에서 전화번호+이 비밀번호로 신청내역을 불러올 수 있습니다.'
              : 'If you submit on someone else’s phone, recover the request on yours with phone + this PIN.'}
          </p>
        </div>
      </div>

      {/* 4. PHOTO: Optional Photo Upload Card */}
      <div className="mb-4">
        <PhotoUploadCard />
      </div>

      {/* STICKY BOTTOM CTA */}
      <div className="fixed bottom-0 left-0 right-0 max-w-md mx-auto p-4 bg-gradient-to-t from-[#111111] via-[#111111]/95 to-transparent safe-bottom z-30">
        <PrimaryCTAButton
          id="btn-submit-dispatch"
          label={t.requestDispatchThisLocation}
          subLabel={
            lang === 'ko'
              ? '비회원 즉시 접수 · 기사님 실시간 배정 시작'
              : 'Instant dispatch · Real-time driver matching'
          }
          onClick={handleSubmit}
        />
      </div>
    </div>
  );
};
