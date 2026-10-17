import React, { useEffect, useState } from 'react';
import {
  Phone,
  PhoneCall,
  CheckCircle2,
  Clock,
  Car,
  MapPin,
  KeyRound,
  Filter,
  LogOut,
  ChevronRight,
  X,
  FileText,
  AlertTriangle,
  Zap,
  ExternalLink,
  UserPlus,
  BarChart3,
} from 'lucide-react';
import { useApp } from '../context/AppContext';
import { displayReceiptNo } from '../lib/dispatchApi';
import { AdminPhotoGallery } from '../components/AdminPhotoGallery';

export const AdminDashboardScreen: React.FC = () => {
  const {
    lang,
    allDispatches,
    isAdminAuthenticated,
    adminUser,
    logoutAdmin,
    navigateTo,
    updateDispatchAdminStatus,
    refreshAdminDispatches,
    showToast,
  } = useApp();

  const [filter, setFilter] = useState<'all' | 'pending' | 'completed'>('all');
  const [confirm, setConfirm] = useState<{
    id: string;
    status: 'pending' | 'contacted' | 'completed';
  } | null>(null);

  const statusLabel = (status: 'pending' | 'contacted' | 'completed') => {
    if (status === 'contacted') return lang === 'ko' ? '처리중' : 'Calling';
    if (status === 'completed') return lang === 'ko' ? '처리완료' : 'Done';
    return lang === 'ko' ? '접수대기' : 'Waiting';
  };

  const askStatus = (id: string, status: 'pending' | 'contacted' | 'completed') => {
    setConfirm({ id, status });
  };

  const saveStatus = () => {
    if (!confirm) return;
    updateDispatchAdminStatus(confirm.id, confirm.status);
    setConfirm(null);
  };

  useEffect(() => {
    if (isAdminAuthenticated) void refreshAdminDispatches();
    // load once when the admin screen opens
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isAdminAuthenticated]);

  // If unauthorized, prompt login
  if (!isAdminAuthenticated) {
    return (
      <div className="p-6 text-center text-white py-20">
        <p className="text-sm text-[#A7A7A7] mb-4">
          {lang === 'ko' ? '관리자 권한이 필요합니다.' : 'Admin authentication required.'}
        </p>
        <button
          onClick={() => navigateTo('admin_login')}
          className="px-4 py-2 rounded-xl bg-[#FFC400] text-black font-bold text-xs"
        >
          {lang === 'ko' ? '로그인 화면으로 이동' : 'Go to Login'}
        </button>
      </div>
    );
  }

  // Filter list
  const filteredDispatches = allDispatches.filter((item) => {
    const status = item.adminCallStatus || 'pending';
    if (filter === 'pending') {
      return status === 'pending' || status === 'contacted';
    }
    if (filter === 'completed') {
      return status === 'completed';
    }
    return true;
  });

  const getStatusBadge = (status?: 'pending' | 'contacted' | 'completed') => {
    switch (status) {
      case 'contacted':
        return (
          <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#3b82f6]/20 text-[#60a5fa] border border-[#3b82f6]/40 flex items-center gap-1">
            <span className="w-1.5 h-1.5 rounded-full bg-[#60a5fa]" />
            {lang === 'ko' ? '처리중' : 'Calling'}
          </span>
        );
      case 'completed':
        return (
          <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#7C3AED]/20 text-[#c4b5fd] border border-[#7C3AED]/40 flex items-center gap-1 whitespace-nowrap">
            <span className="w-1.5 h-1.5 rounded-full bg-[#a78bfa]" />
            {lang === 'ko' ? '처리완료' : 'Completed'}
          </span>
        );
      default:
        return (
          <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FFC400]/20 text-[#FFC400] border border-[#FFC400]/40 flex items-center gap-1">
            <span className="w-1.5 h-1.5 rounded-full bg-[#FFC400] animate-pulse" />
            {lang === 'ko' ? '접수대기' : 'Pending'}
          </span>
        );
    }
  };

  const handleCallCustomer = (phone: string) => {
    const digits = phone.replace(/\D/g, '');
    if (!digits) {
      showToast(lang === 'ko' ? '고객 연락처가 없습니다.' : 'No customer phone provided.');
      return;
    }
    const dial = /^010\d{8}$/.test(digits) ? `+82${digits.slice(1)}` : digits;
    window.open(`tel:${dial}`, '_self');
  };

  const contactKind = (phone: string, channel?: string) => {
    const digits = phone.replace(/\D/g, '');
    if (channel === 'onsite' || /^900\d{8}$/.test(digits)) return 'onsite' as const;
    if (/^010\d{8}$/.test(digits)) return 'kr' as const;
    if (/^09\d{9}$/.test(digits)) return 'ph' as const;
    return 'other' as const;
  };

  const messengerHref = (type?: string, id?: string) => {
    const value = (id || '').trim();
    if (!value) return '';
    if (/^https?:\/\//i.test(value)) return value;
    if (type === 'facebook') {
      const name = value.replace(/^@/, '').replace(/\s+/g, '');
      return name ? `https://m.me/${encodeURIComponent(name)}` : '';
    }
    return '';
  };

  const getMapUrl = (item: (typeof allDispatches)[number]) => {
    const lat = Number(item.latitude);
    const lng = Number(item.longitude);
    const placeName = (item.landmark || '').trim();
    const address = [item.locationAddress, item.locationDetails].filter(Boolean).join(' ').trim();
    const destination =
      Number.isFinite(lat) && Number.isFinite(lng) && lat !== 0 && lng !== 0
        ? `${lat},${lng}`
        : placeName || address || 'Cebu City';
    return `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(destination)}&travelmode=driving`;
  };

  return (
    <div id="screen-admin-dashboard" className="p-4 pb-28 text-white animate-fade-in">
      {/* Top Header Bar */}
      <div className="flex items-center justify-between pb-3 mb-4 border-b border-[#222222]">
        <div>
          <div className="flex items-center gap-2">
            <span className="text-xs font-mono font-bold text-[#FFC400]">CEBU 24 RESCUE</span>
            <span className="text-[10px] px-1.5 py-0.5 rounded bg-[#222222] border border-[#333333] text-[#A7A7A7]">
              관제실
            </span>
          </div>
          <h2 className="text-base font-bold text-white tracking-tight mt-0.5">
            {lang === 'ko' ? '접수 현황 관리자' : 'Dispatch Control Room'}
          </h2>
        </div>

        <div className="flex items-center gap-1.5">
          <button
            type="button"
            onClick={logoutAdmin}
            className="flex items-center gap-1 px-2 py-1.5 rounded-lg bg-[#1C1C1E] border border-[#2c2c2e] text-[#A7A7A7] hover:text-white text-xs font-medium active:scale-95 transition-all"
          >
            <LogOut className="w-3.5 h-3.5" />
            <span>{lang === 'ko' ? '로그아웃' : 'Logout'}</span>
          </button>
        </div>
      </div>

      <p className="text-[11px] text-[#888888] -mt-3 mb-2">
        로그인: {adminUser?.name || adminUser?.id}
      </p>

      <div className="grid grid-cols-2 gap-2 mb-4">
        <button
          type="button"
          onClick={() => navigateTo('admin_stats')}
          className="h-10 rounded-xl bg-[#60a5fa]/10 border border-[#60a5fa]/30 text-[#60a5fa] text-xs font-black flex items-center justify-center gap-1.5"
        >
          <BarChart3 className="w-4 h-4" />
          접수 통계
        </button>
        <button
          type="button"
          onClick={() => navigateTo('admin_register')}
          className="h-10 rounded-xl bg-[#FFC400]/10 border border-[#FFC400]/30 text-[#FFC400] text-xs font-black flex items-center justify-center gap-1.5"
        >
          <UserPlus className="w-4 h-4" />
          관리자 관리
        </button>
      </div>

      {/* Filter Tabs (All / Pending / Completed) */}
      <div className="flex items-center gap-2 mb-4">
        <button
          type="button"
          onClick={() => setFilter('all')}
          className={`flex-1 py-2 rounded-xl text-xs font-bold border transition-all text-center ${
            filter === 'all'
              ? 'bg-[#FFC400] text-black border-[#FFC400]'
              : 'bg-[#1C1C1E] text-[#A7A7A7] border-[#2C2C2E] hover:text-white'
          }`}
        >
          {lang === 'ko' ? `전체 (${allDispatches.length})` : `All (${allDispatches.length})`}
        </button>
        <button
          type="button"
          onClick={() => setFilter('pending')}
          className={`flex-1 py-2 rounded-xl text-xs font-bold border transition-all text-center ${
            filter === 'pending'
              ? 'bg-[#FFC400] text-black border-[#FFC400]'
              : 'bg-[#1C1C1E] text-[#A7A7A7] border-[#2C2C2E] hover:text-white'
          }`}
        >
          {lang === 'ko' ? '접수/처리중' : 'Pending'}
        </button>
        <button
          type="button"
          onClick={() => setFilter('completed')}
          className={`flex-1 py-2 rounded-xl text-xs font-bold border transition-all text-center ${
            filter === 'completed'
              ? 'bg-[#FFC400] text-black border-[#FFC400]'
              : 'bg-[#1C1C1E] text-[#A7A7A7] border-[#2C2C2E] hover:text-white'
          }`}
        >
          {lang === 'ko' ? '완료' : 'Completed'}
        </button>
      </div>

      {/* Dispatches List */}
      <div className="space-y-3">
        {filteredDispatches.length === 0 ? (
          <div className="p-8 text-center bg-[#1C1C1E] border border-[#2C2C2E] rounded-2xl">
            <Clock className="w-8 h-8 text-[#555555] mx-auto mb-2" />
            <p className="text-xs text-[#A7A7A7]">
              {lang === 'ko' ? '해당 필터에 해당하는 접수 내역이 없습니다.' : 'No requests found.'}
            </p>
          </div>
        ) : (
          filteredDispatches.map((item) => (
            <div
              key={item.id}
              className="bg-[#1C1C1E] border border-[#2C2C2E] rounded-2xl p-4 transition-all shadow-md"
            >
              {/* Card Header: ID, Requested Time, Status Badge */}
              <div className="flex items-center justify-between mb-2.5">
                <div className="flex items-center gap-2 min-w-0">
                  <span className="text-sm font-mono font-black text-[#FFC400] shrink-0">
                    {displayReceiptNo(item)}
                  </span>
                  <span className="text-[11px] text-[#A7A7A7] shrink-0">{item.requestedAt}</span>
                </div>
                {getStatusBadge(item.adminCallStatus)}
              </div>
              {item.handledBy && item.adminCallStatus !== 'pending' && (
                <p className="text-[10px] text-[#60a5fa] -mt-1 mb-2">
                  담당 관리자: {item.handledBy}
                </p>
              )}

              {/* Customer Unmasked Details */}
              <div className="bg-[#141416] rounded-xl p-3 border border-[#262628] mb-3 space-y-2">
                {/* Phone & Direct Call */}
                <div className="flex items-center justify-between gap-2">
                  <div className="flex items-center gap-2 min-w-0">
                    <Phone className="w-4 h-4 text-[#22C55E] shrink-0" />
                    <div className="min-w-0">
                      <span className="text-[10px] text-[#888888] block">
                        {contactKind(item.contactPhone, item.contactChannel) === 'onsite'
                          ? '현장 대기 · 조회코드'
                          : contactKind(item.contactPhone, item.contactChannel) === 'kr'
                            ? '한국 연락처'
                            : contactKind(item.contactPhone, item.contactChannel) === 'ph'
                              ? '필리핀 연락처'
                              : '고객 연락처 (가림 없음)'}
                      </span>
                      <span className="text-sm font-bold font-mono text-white">
                        {item.contactPhone || '연락처 미입력'}
                      </span>
                    </div>
                  </div>
                  {item.contactPhone && contactKind(item.contactPhone, item.contactChannel) !== 'onsite' && (
                    <button
                      type="button"
                      onClick={() => handleCallCustomer(item.contactPhone)}
                      className="h-8 px-3 rounded-lg bg-[#22C55E] text-black font-bold text-xs flex items-center gap-1.5 active:scale-95 transition-all shadow-sm shrink-0"
                    >
                      <PhoneCall className="w-3.5 h-3.5" />
                      <span>통화걸기</span>
                    </button>
                  )}
                </div>
                {item.messengerId && (
                  <div className="flex items-center justify-between gap-2">
                    <div className="min-w-0">
                      <span className="text-[10px] text-[#888888] block">
                        {item.messengerType === 'facebook' ? '페이스북 메신저' : '카카오톡'}
                      </span>
                      <span className="text-sm font-bold text-white break-all">{item.messengerId}</span>
                    </div>
                    {messengerHref(item.messengerType, item.messengerId) ? (
                      <a
                        href={messengerHref(item.messengerType, item.messengerId)}
                        target="_blank"
                        rel="noreferrer"
                        className="h-8 px-3 rounded-lg bg-[#1877F2] text-white font-bold text-xs flex items-center shrink-0"
                      >
                        메신저 열기
                      </a>
                    ) : (
                      <button
                        type="button"
                        onClick={() => {
                          void navigator.clipboard?.writeText(item.messengerId || '');
                          showToast('카카오톡 아이디를 복사했습니다. 카카오톡에서 검색해 주세요.');
                        }}
                        className="h-8 px-3 rounded-lg bg-[#FEE500] text-black font-bold text-xs shrink-0"
                      >
                        아이디 복사
                      </button>
                    )}
                  </div>
                )}

                {/* PIN & Vehicle */}
                <div className="grid grid-cols-2 gap-2 pt-1 border-t border-[#222224] text-xs">
                  <div>
                    <span className="text-[10px] text-[#888888] block flex items-center gap-1">
                      <KeyRound className="w-3 h-3 text-[#FFC400]" /> 고객 조회 PIN
                    </span>
                    <span className="font-mono font-bold text-[#FFC400]">
                      {item.customerPin || '1234'}
                    </span>
                  </div>
                  <div>
                    <span className="text-[10px] text-[#888888] block flex items-center gap-1">
                      <Car className="w-3 h-3 text-[#3b82f6]" /> 차종 / 차량번호
                    </span>
                    <span className="font-medium text-white truncate block">
                      {item.vehicleModelName} · {item.plateNumber}
                    </span>
                  </div>
                </div>

                {/* Location */}
                <div className="pt-1 border-t border-[#222224] text-xs">
                  <div className="flex items-center justify-between gap-2">
                    <span className="text-[10px] text-[#888888] flex items-center gap-1">
                      <MapPin className="w-3 h-3 text-[#E53935]" /> 위치 및 랜드마크
                    </span>
                    <a
                      href={getMapUrl(item)}
                      target="_blank"
                      rel="noreferrer"
                      className="shrink-0 flex items-center gap-1 px-2 py-1 rounded-lg bg-[#3b82f6]/15 border border-[#3b82f6]/30 text-[#60a5fa] text-[10px] font-bold"
                    >
                      <ExternalLink className="w-3 h-3" />
                      정확한 GPS 길찾기
                    </a>
                  </div>
                  {item.landmark && (
                    <p className="text-[#FFC400] font-bold text-xs mt-1">
                      고객 지정 장소: {item.landmark}
                    </p>
                  )}
                  <p className="text-white font-medium text-xs mt-0.5">{item.locationAddress}</p>
                  {item.locationDetails && (
                    <p className="text-[#A7A7A7] text-[11px] mt-0.5">{item.locationDetails}</p>
                  )}
                  <p className="text-[9px] text-[#60a5fa] mt-1">
                    길찾기는 고객이 전송한 정확한 GPS 좌표를 기준으로 합니다.
                  </p>
                </div>

                {/* Customer Note */}
                {item.notes && (
                  <div className="pt-1 border-t border-[#222224] text-xs">
                    <span className="text-[10px] text-[#888888] block">고객 요청사항:</span>
                    <p className="text-[#d4d4d8] text-xs bg-[#1a1a1c] p-2 rounded-lg mt-1">
                      {item.notes}
                    </p>
                  </div>
                )}
                <AdminPhotoGallery photoIds={item.photos || []} lazy={item.adminCallStatus === 'completed'} />
              </div>

              {/* Status Update Quick Buttons */}
              <div className="grid grid-cols-3 gap-2">
                <button
                  type="button"
                  onClick={() => askStatus(item.id, 'pending')}
                  className={`py-1.5 rounded-lg text-[11px] font-bold border transition-all whitespace-nowrap ${
                    !item.adminCallStatus || item.adminCallStatus === 'pending'
                      ? 'bg-[#FFC400] text-black border-[#FFC400]'
                      : 'bg-[#222224] text-[#A7A7A7] border-[#333336]'
                  }`}
                >
                  {lang === 'ko' ? '1. 접수대기' : '1. Waiting'}
                </button>
                <button
                  type="button"
                  onClick={() => askStatus(item.id, 'contacted')}
                  className={`py-1.5 rounded-lg text-[11px] font-bold border transition-all whitespace-nowrap ${
                    item.adminCallStatus === 'contacted'
                      ? 'bg-[#3b82f6] text-white border-[#3b82f6]'
                      : 'bg-[#222224] text-[#A7A7A7] border-[#333336]'
                  }`}
                >
                  {lang === 'ko' ? '2. 처리중' : '2. Calling'}
                </button>
                <button
                  type="button"
                  onClick={() => askStatus(item.id, 'completed')}
                  className={`py-1.5 rounded-lg text-[11px] font-bold border transition-all whitespace-nowrap ${
                    item.adminCallStatus === 'completed'
                      ? 'bg-[#7C3AED] text-white border-[#7C3AED]'
                      : 'bg-[#222224] text-[#A7A7A7] border-[#333336]'
                  }`}
                >
                  {lang === 'ko' ? '3. 처리완료' : '3. Done'}
                </button>
              </div>
            </div>
          ))
        )}
      </div>

      {confirm && (
        <div
          id="admin-status-confirm"
          className="fixed inset-0 z-50 flex items-center justify-center p-5 bg-black/75"
          onClick={() => setConfirm(null)}
        >
          <div
            className="w-full max-w-sm rounded-3xl bg-[#1C1C1E] border border-[#2c2c2e] p-5 shadow-2xl"
            onClick={(e) => e.stopPropagation()}
          >
            <p className="text-base font-black text-white text-center">
              {lang === 'ko'
                ? `${statusLabel(confirm.status)}${
                    confirm.status === 'contacted' ? '으로' : '로'
                  } 저장할까요?`
                : `Save as ${statusLabel(confirm.status)}?`}
            </p>
            <div className="grid grid-cols-2 gap-2 mt-4">
              <button
                type="button"
                onClick={() => setConfirm(null)}
                className="min-h-[48px] rounded-2xl bg-[#2a2a2c] text-white text-sm font-bold"
              >
                {lang === 'ko' ? '취소' : 'Cancel'}
              </button>
              <button
                type="button"
                onClick={saveStatus}
                className="min-h-[48px] rounded-2xl bg-[#FFC400] text-black text-sm font-black"
              >
                {lang === 'ko' ? '저장' : 'Save'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
