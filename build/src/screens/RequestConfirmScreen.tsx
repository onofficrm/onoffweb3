import React, { useState } from 'react';
import {
  MapPin,
  Car,
  Phone,
  Edit3,
  ShieldCheck,
  AlertTriangle,
  Zap,
  Clock,
  Compass,
  CreditCard,
  Banknote,
  CheckCircle2,
  HelpCircle,
  CarFront,
  Truck,
  Bus,
} from 'lucide-react';
import { useApp } from '../context/AppContext';
import { getTranslation } from '../data/translations';
import { SERVICES_DATA } from '../data/mockData';
import { PaymentMethodId } from '../types';
import { isOnsiteLookupCode } from '../lib/dispatchApi';

export const RequestConfirmScreen: React.FC = () => {
  const {
    lang,
    selectedService,
    locationAddress,
    landmark,
    selectedVehicleType,
    vehicleModelName,
    plateNumber,
    contactPhone,
    paymentMethod,
    setPaymentMethod,
    submitDispatchRequest,
    isDispatchSubmitting,
    dispatchUploadProgress,
    dispatchSubmitError,
    clearDispatchSubmitError,
    navigateTo,
  } = useApp();

  const t = getTranslation(lang);
  const [showConfirmModal, setShowConfirmModal] = useState<boolean>(false);

  const service = selectedService || SERVICES_DATA[0];

  // Helper for vehicle icon & label
  const getVehicleDisplay = () => {
    switch (selectedVehicleType) {
      case 'sedan':
        return { label: 'Sedan (세단)', icon: <Car className="w-4 h-4 text-[#3b82f6]" />, emoji: '🚗' };
      case 'suv':
        return { label: 'SUV', icon: <CarFront className="w-4 h-4 text-[#3b82f6]" />, emoji: '🚙' };
      case 'van':
        return { label: 'Van (승합)', icon: <Bus className="w-4 h-4 text-[#3b82f6]" />, emoji: '🚐' };
      case 'pickup':
        return { label: 'Pickup (픽업)', icon: <Truck className="w-4 h-4 text-[#3b82f6]" />, emoji: '🛻' };
      case 'truck':
        return { label: 'Truck (트럭)', icon: <Truck className="w-4 h-4 text-[#3b82f6]" />, emoji: '🚛' };
      default:
        return { label: '기타 차량', icon: <HelpCircle className="w-4 h-4 text-[#3b82f6]" />, emoji: '🚙' };
    }
  };

  const vehicleInfo = getVehicleDisplay();

  // Price calculation
  const isTowing = service.id === 'towing';
  const basePrice = service.basePricePhp || 1500;

  // Handles confirming emergency dispatch
  const handleConfirmDispatch = async () => {
    const success = await submitDispatchRequest();
    if (success) {
      setShowConfirmModal(false);
      navigateTo('driver_assigned');
    }
  };

  return (
    <div id="screen-request-confirm" className="pb-36 p-4 animate-fade-in text-white">
      {/* 1. Header Title & Step Indicator */}
      <div className="mb-4">
        <div className="flex items-center justify-between mb-1">
          <div className="flex items-center gap-1.5 text-xs font-black text-[#FFC400]">
            <span className="w-2 h-2 rounded-full bg-[#FFC400] animate-pulse" />
            <span>FINAL STEP · 출동 요청 확인</span>
          </div>
          <span className="text-[11px] font-bold px-2 py-0.5 rounded-full bg-[#22C55E]/15 text-[#22C55E] border border-[#22C55E]/30">
            {lang === 'ko' ? '세부 전지역 신속 출동' : 'Fast Cebu Dispatch'}
          </span>
        </div>
        <h2 className="text-xl font-black tracking-tight text-white">
          {lang === 'ko' ? '출동 요청 확인' : 'Confirm Rescue Request'}
        </h2>
        <p className="text-xs text-[#A7A7A7] mt-0.5">
          {lang === 'ko'
            ? '입력하신 출동 정보를 확인 후 요청 버튼을 눌러주세요.'
            : 'Please review your dispatch details before submitting.'}
        </p>
      </div>

      {/* 2. SUMMARY CARD (Summary Section with individual [수정] edit buttons) */}
      <div className="bg-[#1C1C1E] border border-[#2c2c2e] rounded-3xl p-4 mb-4 shadow-xl divide-y divide-[#28282b]">
        {/* 서비스 */}
        <div className="flex items-center justify-between py-3 first:pt-1">
          <div className="flex items-center gap-3 min-w-0">
            <div className="w-10 h-10 rounded-2xl bg-[#FFC400]/15 border border-[#FFC400]/30 flex items-center justify-center shrink-0 shadow-inner">
              <Zap className="w-5 h-5 text-[#FFC400] fill-[#FFC400]/20" />
            </div>
            <div className="min-w-0">
              <span className="text-[11px] font-bold text-[#A7A7A7] uppercase tracking-wider block">
                {lang === 'ko' ? '서비스' : 'Service'}
              </span>
              <p className="text-sm font-black text-white flex items-center gap-1.5 truncate">
                <span>{service.id === 'battery' ? '🔋' : service.id === 'tire' ? '🛞' : service.id === 'towing' ? '🪝' : service.id === 'lockout' ? '🔑' : '🚨'}</span>
                <span>{lang === 'ko' ? service.nameKo : service.nameEn}</span>
              </p>
            </div>
          </div>
          <button
            type="button"
            id="summary-edit-service-btn"
            onClick={() => navigateTo('service_select')}
            className="px-2.5 py-1 rounded-xl bg-[#28282b] hover:bg-[#343438] text-[11px] font-bold text-[#FFC400] border border-[#3a3a3d] shrink-0 active:scale-95 transition-all flex items-center gap-1"
          >
            <Edit3 className="w-3 h-3" />
            <span>{lang === 'ko' ? '수정' : 'Edit'}</span>
          </button>
        </div>

        {/* 위치 */}
        <div className="flex items-center justify-between py-3">
          <div className="flex items-start gap-3 min-w-0">
            <div className="w-10 h-10 rounded-2xl bg-[#E53935]/15 border border-[#E53935]/30 flex items-center justify-center shrink-0 mt-0.5">
              <MapPin className="w-5 h-5 text-[#E53935]" />
            </div>
            <div className="min-w-0">
              <span className="text-[11px] font-bold text-[#A7A7A7] uppercase tracking-wider block">
                {lang === 'ko' ? '위치' : 'Location'}
              </span>
              <p className="text-sm font-black text-white truncate">
                📍 {locationAddress || 'Banilad, Cebu City'}
              </p>
            </div>
          </div>
          <button
            type="button"
            id="summary-edit-location-btn"
            onClick={() => navigateTo('location_confirm')}
            className="px-2.5 py-1 rounded-xl bg-[#28282b] hover:bg-[#343438] text-[11px] font-bold text-[#FFC400] border border-[#3a3a3d] shrink-0 active:scale-95 transition-all flex items-center gap-1"
          >
            <Edit3 className="w-3 h-3" />
            <span>{lang === 'ko' ? '수정' : 'Edit'}</span>
          </button>
        </div>

        {/* 차량 */}
        <div className="flex items-center justify-between py-3">
          <div className="flex items-center gap-3 min-w-0">
            <div className="w-10 h-10 rounded-2xl bg-[#3b82f6]/15 border border-[#3b82f6]/30 flex items-center justify-center shrink-0">
              {vehicleInfo.icon}
            </div>
            <div className="min-w-0">
              <span className="text-[11px] font-bold text-[#A7A7A7] uppercase tracking-wider block">
                {lang === 'ko' ? '차량' : 'Vehicle'}
              </span>
              <p className="text-sm font-black text-white flex items-center gap-1.5 truncate">
                <span>{vehicleInfo.emoji}</span>
                <span>{vehicleInfo.label}</span>
                {vehicleModelName && (
                  <span className="text-xs font-medium text-[#A7A7A7]">
                    · {vehicleModelName}
                  </span>
                )}
              </p>
            </div>
          </div>
          <button
            type="button"
            id="summary-edit-vehicle-btn"
            onClick={() => navigateTo('request_info')}
            className="px-2.5 py-1 rounded-xl bg-[#28282b] hover:bg-[#343438] text-[11px] font-bold text-[#FFC400] border border-[#3a3a3d] shrink-0 active:scale-95 transition-all flex items-center gap-1"
          >
            <Edit3 className="w-3 h-3" />
            <span>{lang === 'ko' ? '수정' : 'Edit'}</span>
          </button>
        </div>

        {/* 전화번호 */}
        <div className="flex items-center justify-between py-3">
          <div className="flex items-center gap-3 min-w-0">
            <div className="w-10 h-10 rounded-2xl bg-[#22C55E]/15 border border-[#22C55E]/30 flex items-center justify-center shrink-0">
              <Phone className="w-5 h-5 text-[#22C55E]" />
            </div>
            <div className="min-w-0">
              <span className="text-[11px] font-bold text-[#A7A7A7] uppercase tracking-wider block">
                {lang === 'ko' ? '전화번호' : 'Contact Phone'}
              </span>
              <p className="text-sm font-black text-white truncate font-mono">
                {isOnsiteLookupCode(contactPhone)
                  ? lang === 'ko'
                    ? `현장 대기 · ${contactPhone}`
                    : `On-site wait · ${contactPhone}`
                  : `📞 ${contactPhone || (lang === 'ko' ? '연락처 미입력' : 'Phone not entered')}`}
              </p>
            </div>
          </div>
          <button
            type="button"
            id="summary-edit-phone-btn"
            onClick={() => navigateTo('request_info')}
            className="px-2.5 py-1 rounded-xl bg-[#28282b] hover:bg-[#343438] text-[11px] font-bold text-[#FFC400] border border-[#3a3a3d] shrink-0 active:scale-95 transition-all flex items-center gap-1"
          >
            <Edit3 className="w-3 h-3" />
            <span>{lang === 'ko' ? '수정' : 'Edit'}</span>
          </button>
        </div>

        {/* Landmark */}
        <div className="flex items-center justify-between py-3 last:pb-1">
          <div className="flex items-center gap-3 min-w-0">
            <div className="w-10 h-10 rounded-2xl bg-[#8b5cf6]/15 border border-[#8b5cf6]/30 flex items-center justify-center shrink-0">
              <Compass className="w-5 h-5 text-[#8b5cf6]" />
            </div>
            <div className="min-w-0">
              <span className="text-[11px] font-bold text-[#A7A7A7] uppercase tracking-wider block">
                {lang === 'ko' ? 'Landmark (기사 위치 참고)' : 'Landmark Reference'}
              </span>
              <p className="text-xs font-bold text-white truncate">
                {landmark ? `🏛️ ${landmark}` : (lang === 'ko' ? '지정된 위치' : 'Selected area')}
              </p>
            </div>
          </div>
          <button
            type="button"
            id="summary-edit-landmark-btn"
            onClick={() => navigateTo('location_confirm')}
            className="px-2.5 py-1 rounded-xl bg-[#28282b] hover:bg-[#343438] text-[11px] font-bold text-[#FFC400] border border-[#3a3a3d] shrink-0 active:scale-95 transition-all flex items-center gap-1"
          >
            <Edit3 className="w-3 h-3" />
            <span>{lang === 'ko' ? '수정' : 'Edit'}</span>
          </button>
        </div>
      </div>

      {/* 3. PAYMENT (Cash vs GCash Payment Cards) */}
      <div className="mb-4">
        <label className="text-xs font-bold text-white mb-2 flex items-center justify-between">
          <span>{lang === 'ko' ? '결제 방법 선택' : 'Select Payment Method'}</span>
          <span className="text-[10px] text-[#A7A7A7]">{lang === 'ko' ? '현장 조치 완료 후 결제' : 'Pay after service'}</span>
        </label>

        <div className="grid grid-cols-2 gap-2.5">
          {/* Option 1: Cash */}
          <div
            id="payment-card-cash"
            onClick={() => setPaymentMethod('cash')}
            className={`p-3.5 rounded-2xl border transition-all cursor-pointer flex flex-col justify-between min-h-[90px] active:scale-[0.98] ${
              paymentMethod === 'cash'
                ? 'bg-[#1C1C1E] border-[#22C55E] shadow-[0_0_15px_rgba(34,197,94,0.25)]'
                : 'bg-[#161618] border-[#292929] hover:border-[#383838]'
            }`}
          >
            <div className="flex items-center justify-between">
              <div className="w-9 h-9 rounded-xl bg-[#22C55E]/15 text-[#22C55E] flex items-center justify-center font-bold">
                <Banknote className="w-5 h-5" />
              </div>
              {paymentMethod === 'cash' && (
                <CheckCircle2 className="w-4 h-4 text-[#22C55E] fill-[#22C55E]/20" />
              )}
            </div>
            <div>
              <p className="text-sm font-extrabold text-white">Cash</p>
              <p className="text-[11px] text-[#A7A7A7]">
                {lang === 'ko' ? '기사님 현장 현금(₱)' : 'Pay cash to driver'}
              </p>
            </div>
          </div>

          {/* Option 2: GCash */}
          <div
            id="payment-card-gcash"
            onClick={() => setPaymentMethod('gcash')}
            className={`p-3.5 rounded-2xl border transition-all cursor-pointer flex flex-col justify-between min-h-[90px] active:scale-[0.98] ${
              paymentMethod === 'gcash'
                ? 'bg-[#1C1C1E] border-[#007DFE] shadow-[0_0_15px_rgba(0,125,254,0.25)]'
                : 'bg-[#161618] border-[#292929] hover:border-[#383838]'
            }`}
          >
            <div className="flex items-center justify-between">
              <div className="w-9 h-9 rounded-xl bg-[#007DFE]/20 text-[#007DFE] flex items-center justify-center font-black text-xs">
                G
              </div>
              {paymentMethod === 'gcash' && (
                <CheckCircle2 className="w-4 h-4 text-[#007DFE] fill-[#007DFE]/20" />
              )}
            </div>
            <div>
              <div className="flex items-center gap-1">
                <p className="text-sm font-extrabold text-white">GCash</p>
                <span className="px-1.5 py-0.2 rounded text-[9px] font-bold bg-[#007DFE]/20 text-[#007DFE]">
                  QR 송금
                </span>
              </div>
              <p className="text-[11px] text-[#A7A7A7]">
                {lang === 'ko' ? '필리핀 모바일 결제' : 'Philippine QR e-wallet'}
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* 4. PRICE NOTICE CARD */}
      <div className="bg-[#1C1C1E] border border-[#2c2c2e] rounded-2xl p-4 mb-4 space-y-2">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-1.5">
            <span className="text-xs font-bold text-[#A7A7A7]">출동비 안내</span>
          </div>
          <span className="text-base font-black text-[#FFC400]">
            출동비 ₱{basePrice.toLocaleString()}부터
          </span>
        </div>

        <div className="p-2.5 rounded-xl bg-[#242426] border border-[#333336] text-xs text-[#A7A7A7] leading-relaxed">
          {isTowing ? (
            <p className="text-white/90">
              ⚠️ <span className="font-bold text-[#FFC400]">견인 안내:</span> 견인 거리와 차량 상태에 따라 금액이 달라질 수 있습니다.
            </p>
          ) : (
            <p className="text-white/90">
              ℹ️ 현장 위치와 서비스 내용에 따라 최종 금액을 안내드립니다.
            </p>
          )}
        </div>

        <div className="flex items-center justify-between text-[11px] text-[#A7A7A7] pt-1">
          <span className="flex items-center gap-1">
            <Clock className="w-3.5 h-3.5 text-[#FFC400]" />
            <span>{t.estArrivalValue}</span>
          </span>
          <span className="text-[#22C55E] font-bold">신속 출동</span>
        </div>
      </div>

      {/* 5. STICKY EMERGENCY CTA: 🚨 출동 요청하기 */}
      <div className="fixed bottom-0 left-0 right-0 max-w-md mx-auto p-4 bg-gradient-to-t from-[#111111] via-[#111111]/98 to-transparent safe-bottom z-30">
        <button
          type="button"
          id="confirm-emergency-cta-btn"
          onClick={() => {
            clearDispatchSubmitError();
            setShowConfirmModal(true);
          }}
          disabled={isDispatchSubmitting}
          className="w-full min-h-[64px] rounded-2xl font-black text-lg bg-[#E53935] hover:bg-[#d32f2f] text-white shadow-[0_0_30px_rgba(229,57,53,0.5)] active:scale-[0.98] transition-all flex flex-col items-center justify-center px-4"
        >
          <div className="flex items-center gap-2">
            <span className="text-xl">🚨</span>
            <span className="tracking-wide">
              {lang === 'ko' ? '출동 요청하기' : 'Request Emergency Dispatch'}
            </span>
          </div>
          <span className="text-xs font-bold text-white/90 mt-0.5">
            {lang === 'ko' ? '인근 긴급 구난 기사 즉시 배정 시작' : 'Immediate mechanic assignment initiated'}
          </span>
        </button>
      </div>

      {/* 6. CONFIRM MODAL (사용자가 누르면 Confirm Modal 표시) */}
      {showConfirmModal && (
        <div
          id="confirm-dispatch-modal-backdrop"
          className="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fade-in"
        >
          <div
            id="confirm-dispatch-modal"
            className="w-full max-w-sm bg-[#1C1C1E] border border-[#333336] rounded-3xl p-5 shadow-2xl space-y-4 animate-scale-up text-center"
          >
            <div className="w-14 h-14 rounded-full bg-[#E53935]/20 border-2 border-[#E53935] text-[#E53935] flex items-center justify-center mx-auto shadow-[0_0_20px_rgba(229,57,53,0.3)]">
              <AlertTriangle className="w-7 h-7" />
            </div>

            <div>
              <h3 className="text-lg font-black text-white">
                이 위치로 긴급출동을 요청하시겠습니까?
              </h3>
              <p className="text-xs text-[#A7A7A7] mt-1.5 leading-relaxed">
                📍 <strong className="text-white">{locationAddress || 'Banilad, Cebu City'}</strong>
                <br />
                요청 즉시 인근 가장 가까운 긴급 구난차가 배정됩니다.
              </p>
              {dispatchSubmitError && !isDispatchSubmitting && (
                <p
                  id="dispatch-submit-error"
                  role="alert"
                  className="mt-3 rounded-xl border border-[#E53935]/50 bg-[#E53935]/15 px-3 py-2 text-xs font-bold leading-relaxed text-[#ff8a80]"
                >
                  {dispatchSubmitError}
                </p>
              )}
              {isDispatchSubmitting && (
                <div className="mt-4">
                  <p className="text-sm font-bold text-[#FFC400]">
                    {lang === 'ko' ? '사진 업로드 및 접수 중…' : 'Uploading photos and request…'}
                  </p>
                  <div className="h-2 mt-2 overflow-hidden rounded-full bg-[#292929]">
                    <div
                      className="h-full bg-[#FFC400] transition-all"
                      style={{ width: `${Math.max(4, dispatchUploadProgress)}%` }}
                    />
                  </div>
                  <p className="mt-1 text-xs text-[#A7A7A7]">{dispatchUploadProgress}%</p>
                </div>
              )}
            </div>

            <div className="grid grid-cols-2 gap-2.5 pt-1">
              <button
                type="button"
                id="modal-cancel-btn"
                onClick={() => setShowConfirmModal(false)}
                disabled={isDispatchSubmitting}
                className="py-3.5 rounded-xl bg-[#292929] hover:bg-[#333333] text-white font-bold text-sm transition-all disabled:opacity-50"
              >
                {lang === 'ko' ? '취소' : 'Cancel'}
              </button>

              <button
                type="button"
                id="modal-confirm-dispatch-btn"
                onClick={handleConfirmDispatch}
                disabled={isDispatchSubmitting}
                className="py-3.5 rounded-xl bg-[#E53935] hover:bg-[#d32f2f] text-white font-black text-sm shadow-[0_0_15px_rgba(229,57,53,0.4)] transition-all flex items-center justify-center gap-1.5 disabled:opacity-60"
              >
                <span>🚨</span>
                <span>
                  {isDispatchSubmitting
                    ? lang === 'ko' ? '접수 중…' : 'Submitting…'
                    : lang === 'ko' ? '출동 요청' : 'Confirm'}
                </span>
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
