import { useTranslation } from 'react-i18next'

export default function Dashboard() {
  const { t } = useTranslation()

  return (
    <div>
      <h1 className="text-3xl font-bold mb-8">{t('dashboard')}</h1>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="text-gray-500 text-sm font-medium">{t('total_channels')}</h3>
          <p className="text-3xl font-bold mt-2">0</p>
        </div>

        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="text-gray-500 text-sm font-medium">{t('active_channels')}</h3>
          <p className="text-3xl font-bold mt-2">0</p>
        </div>

        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="text-gray-500 text-sm font-medium">{t('total_admins')}</h3>
          <p className="text-3xl font-bold mt-2">1</p>
        </div>
      </div>

      <div className="mt-8 bg-white p-6 rounded-lg shadow">
        <h2 className="text-xl font-semibold mb-4">{t('recent_imports')}</h2>
        <p className="text-gray-500">{t('info')}: Start by importing M3U files from the Channels menu</p>
      </div>
    </div>
  )
}
