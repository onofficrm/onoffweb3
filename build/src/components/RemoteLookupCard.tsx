import React, { useState } from 'react';
import { Smartphone, Search, Loader2 } from 'lucide-react';
import { useApp } from '../context/AppContext';
import { DEFAULT_VIEW_PIN } from '../lib/dispatchStorage';
import { formatPhoneInput, isDispatchPhone, isKoreanPhone } from '../lib/dispatchApi';

/** Lookup requests made on another (borrowed) phone via phone + PIN */
export const RemoteLookupCard: React.FC<{ id?: string }> = ({
  id = 'remote-lookup-card',
}) => {
  const { lang, lookupRemoteRequests, showToast } = useApp();
  const [open, setOpen] = useState(false);
  const [phone, setPhone] = useState('');
  const [pin, setPin] = useState('');
  const [loading, setLoading] = useState(false);

  const handlePhone = (val: string) => {
    const country = isKoreanPhone(val) || val.replace(/\D/g, '').startsWith('010') || val.replace(/\D/g, '').startsWith('10')
      ? 'kr'
      : 'ph';
    setPhone(formatPhoneInput(val, country));
  };

  const handleLookup = async () => {
    if (!isDispatchPhone(phone) || pin.length !== 4) {
      showToast(
        lang === 'ko'
          ? '필리핀(09), 한국(010) 번호 또는 현장대기 조회코드와 비밀번호 4자리를 입력해 주세요'
          : 'Enter a PH, KR, or on-site code plus the 4-digit PIN'
      );
      return;
    }
    setLoading(true);
    const result = await lookupRemoteRequests(phone, pin);
    setLoading(false);
    if (result.ok) {
      setOpen(false);
      setPin('');
    }
  };

  return (
    <div
      id={id}
      className="w-full rounded-2xl bg-[#1C1C1E] border border-[#2c2c2e] overflow-hidden"
    >
      <button
        type="button"
        id={`${id}-toggle`}
        onClick={() => setOpen((v) => !v)}
        className="w-full text-left p-3.5 flex items-start gap-3 active:bg-[#252528] transition-colors"
      >
        <div className="w-10 h-10 rounded-xl bg-[#22C55E]/15 text-[#22C55E] flex items-center justify-center shrink-0">
          <Smartphone className="w-5 h-5" />
        </div>
        <div className="min-w-0">
          <p className="text-xs font-black text-white">
            {lang === 'ko'
              ? '📱 다른 기기에서 신청한 내역 조회'
              : '📱 Lookup request from another device'}
          </p>
          <p className="text-[11px] text-[#A7A7A7] mt-0.5 leading-relaxed">
            {lang === 'ko'
              ? `신청한 번호(필리핀·한국) 또는 현장대기 조회코드 + 비밀번호(${DEFAULT_VIEW_PIN})로 불러옵니다`
              : `Use the phone + PIN (default ${DEFAULT_VIEW_PIN}) from when you submitted`}
          </p>
        </div>
      </button>

      {open && (
        <div className="px-3.5 pb-3.5 space-y-2 border-t border-[#2c2c2e] pt-3">
          <input
            id={`${id}-phone`}
            type="tel"
            inputMode="numeric"
            value={phone}
            onChange={(e) => handlePhone(e.target.value)}
            placeholder={lang === 'ko' ? '09 / 010 / 조회코드' : '09 / 010 / lookup code'}
            className="w-full h-11 rounded-xl bg-[#0b0b0c] border border-[#333] px-3 text-sm text-white outline-none focus:border-[#22C55E]"
          />
          <input
            id={`${id}-pin`}
            type="password"
            inputMode="numeric"
            maxLength={4}
            value={pin}
            onChange={(e) => setPin(e.target.value.replace(/\D/g, '').slice(0, 4))}
            placeholder={lang === 'ko' ? '비밀번호 4자리' : '4-digit PIN'}
            className="w-full h-11 rounded-xl bg-[#0b0b0c] border border-[#333] px-3 text-sm text-white tracking-[0.3em] text-center outline-none focus:border-[#22C55E]"
          />
          <button
            type="button"
            id={`${id}-submit`}
            disabled={loading}
            onClick={() => void handleLookup()}
            className="w-full min-h-[44px] rounded-xl bg-[#22C55E] text-black font-black text-sm flex items-center justify-center gap-2 disabled:opacity-60"
          >
            {loading ? (
              <Loader2 className="w-4 h-4 animate-spin" />
            ) : (
              <Search className="w-4 h-4" />
            )}
            {lang === 'ko' ? '내 폰으로 불러오기' : 'Sync to this phone'}
          </button>
        </div>
      )}
    </div>
  );
};
