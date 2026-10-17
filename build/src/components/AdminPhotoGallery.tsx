import React, { useEffect, useRef, useState } from 'react';
import { ChevronLeft, ChevronRight, Image, X } from 'lucide-react';
import { useApp } from '../context/AppContext';

interface AdminPhotoGalleryProps {
  photoIds: string[];
}

export const AdminPhotoGallery: React.FC<AdminPhotoGalleryProps> = ({ photoIds }) => {
  const { lang, loadAdminPhoto } = useApp();
  const [urls, setUrls] = useState<string[]>([]);
  const [loading, setLoading] = useState(false);
  const [selectedIndex, setSelectedIndex] = useState<number | null>(null);
  const [visibleIndex, setVisibleIndex] = useState(0);
  const scrollerRef = useRef<HTMLDivElement>(null);
  const photoKey = photoIds.join(',');

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    Promise.all(photoIds.map((id) => loadAdminPhoto(id))).then((loaded) => {
      const valid = loaded.filter((url): url is string => Boolean(url));
      if (cancelled) {
        valid.forEach((url) => URL.revokeObjectURL(url));
        return;
      }
      setUrls(valid);
      setLoading(false);
    });
    return () => {
      cancelled = true;
    };
  }, [photoKey]);

  useEffect(
    () => () => {
      urls.forEach((url) => URL.revokeObjectURL(url));
    },
    [urls]
  );

  useEffect(() => {
    if (selectedIndex == null) return;
    const scroller = scrollerRef.current;
    if (!scroller) return;
    const frame = window.requestAnimationFrame(() => {
      scroller.scrollTo({ left: selectedIndex * scroller.clientWidth, behavior: 'auto' });
    });
    return () => window.cancelAnimationFrame(frame);
  }, [selectedIndex]);

  const closeViewer = () => setSelectedIndex(null);

  const scrollToIndex = (index: number) => {
    const scroller = scrollerRef.current;
    if (!scroller || urls.length === 0) return;
    const next = Math.max(0, Math.min(urls.length - 1, index));
    scroller.scrollTo({ left: next * scroller.clientWidth, behavior: 'smooth' });
    setVisibleIndex(next);
  };

  if (photoIds.length === 0) return null;

  return (
    <div className="pt-2 border-t border-[#222224]">
      <p className="flex items-center gap-1.5 text-xs font-bold text-[#FFC400]">
        <Image className="w-4 h-4" />
        {lang === 'ko' ? `접수 사진 ${photoIds.length}장` : `${photoIds.length} photos`}
      </p>

      <div className="mt-2">
          {loading ? (
            <p className="text-xs text-[#A7A7A7]">{lang === 'ko' ? '사진 불러오는 중…' : 'Loading photos…'}</p>
          ) : urls.length === 0 ? (
            <p className="text-xs text-[#E57373]">{lang === 'ko' ? '사진을 불러오지 못했습니다.' : 'Could not load photos.'}</p>
          ) : (
            <div className="grid grid-cols-3 gap-2">
              {urls.map((url, index) => (
                <button
                  type="button"
                  key={url}
                  onClick={() => {
                    setVisibleIndex(index);
                    setSelectedIndex(index);
                  }}
                  className="overflow-hidden rounded-xl border border-[#3A3A3C] bg-black aspect-square"
                >
                  <img src={url} alt={`Dispatch attachment ${index + 1}`} className="object-cover w-full h-full" />
                </button>
              ))}
            </div>
          )}
      </div>

      {selectedIndex != null && urls.length > 0 && (
        <div className="fixed inset-0 z-[80] bg-black" role="dialog" aria-modal="true">
          <div
            ref={scrollerRef}
            className="flex h-full w-full overflow-x-auto snap-x snap-mandatory"
            onScroll={(event) => {
              const width = event.currentTarget.clientWidth;
              if (width <= 0) return;
              setVisibleIndex(Math.round(event.currentTarget.scrollLeft / width));
            }}
          >
            {urls.map((url, index) => (
              <div key={url} className="flex h-full w-full shrink-0 snap-center items-center justify-center px-3">
                <img
                  src={url}
                  alt={`Dispatch attachment ${index + 1}`}
                  className="max-h-[78vh] max-w-full object-contain"
                />
              </div>
            ))}
          </div>

          <button
            type="button"
            aria-label={lang === 'ko' ? '닫기' : 'Close'}
            onClick={closeViewer}
            className="absolute right-3 flex h-14 w-14 items-center justify-center rounded-full bg-white text-black shadow-lg"
            style={{ top: 'max(16px, env(safe-area-inset-top))' }}
          >
            <X className="h-8 w-8" />
          </button>

          <div
            className="pointer-events-none absolute left-1/2 -translate-x-1/2 rounded-full bg-black/70 px-3 py-1 text-sm font-bold text-white"
            style={{ top: 'max(28px, calc(env(safe-area-inset-top) + 12px))' }}
          >
            {visibleIndex + 1} / {urls.length}
          </div>

          {urls.length > 1 && (
            <>
              <button
                type="button"
                aria-label={lang === 'ko' ? '이전 사진' : 'Previous photo'}
                onClick={() => scrollToIndex(visibleIndex - 1)}
                className="absolute left-2 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-black"
              >
                <ChevronLeft className="h-7 w-7" />
              </button>
              <button
                type="button"
                aria-label={lang === 'ko' ? '다음 사진' : 'Next photo'}
                onClick={() => scrollToIndex(visibleIndex + 1)}
                className="absolute right-2 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-black"
              >
                <ChevronRight className="h-7 w-7" />
              </button>
            </>
          )}

          <button
            type="button"
            onClick={closeViewer}
            className="absolute left-1/2 flex min-h-[56px] min-w-[180px] -translate-x-1/2 items-center justify-center rounded-2xl bg-[#FFC400] px-8 text-lg font-black text-black shadow-lg"
            style={{ bottom: 'max(20px, env(safe-area-inset-bottom))' }}
          >
            {lang === 'ko' ? '닫기' : 'Close'}
          </button>
        </div>
      )}
    </div>
  );
};
