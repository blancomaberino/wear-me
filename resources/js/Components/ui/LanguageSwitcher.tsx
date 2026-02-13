import { router, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import i18n from '@/i18n';

const locales = [
  { code: 'en', label: 'EN', flag: '🇺🇸' },
  { code: 'es', label: 'ES', flag: '🇪🇸' },
] as const;

interface LanguageSwitcherProps {
  className?: string;
}

export function LanguageSwitcher({ className }: LanguageSwitcherProps) {
  const { locale } = usePage().props as any;
  const currentLocale = locale || 'en';

  const handleSwitch = (newLocale: string) => {
    if (newLocale === currentLocale) return;
    // Change language immediately on the client for instant feedback
    i18n.changeLanguage(newLocale);
    // Persist to server
    router.post(route('locale.update'), { locale: newLocale }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  return (
    <div className={cn('flex items-center gap-1 rounded-pill bg-surface-100 p-0.5', className)}>
      {locales.map((loc) => (
        <button
          key={loc.code}
          onClick={() => handleSwitch(loc.code)}
          className={cn(
            'flex items-center gap-1 px-2.5 py-1 rounded-pill text-caption font-medium transition-colors',
            currentLocale === loc.code
              ? 'bg-white text-surface-900 shadow-xs'
              : 'text-surface-500 hover:text-surface-700',
          )}
        >
          <span className="text-xs">{loc.flag}</span>
          {loc.label}
        </button>
      ))}
    </div>
  );
}
