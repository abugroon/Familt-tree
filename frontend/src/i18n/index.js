import { createI18n } from 'vue-i18n'
import ar from './locales/ar'
import en from './locales/en'

const savedLocale = localStorage.getItem('locale') || 'ar'

export const i18n = createI18n({
  legacy: false,
  locale: savedLocale,
  fallbackLocale: 'en',
  messages: { ar, en },
})

// Apply RTL/LTR on boot
export function applyLocaleDir(locale) {
  document.documentElement.dir  = locale === 'ar' ? 'rtl' : 'ltr'
  document.documentElement.lang = locale
}

applyLocaleDir(savedLocale)
