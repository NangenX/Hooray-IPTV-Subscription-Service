import { useTranslation } from 'react-i18next'

export default function ChannelList() {
  const { t } = useTranslation()

  return (
    <div>
      <div className="flex justify-between items-center mb-8">
        <h1 className="text-3xl font-bold">{t('channel_list')}</h1>
        <button className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
          {t('add_channel')}
        </button>
      </div>

      <div className="bg-white p-6 rounded-lg shadow">
        <div className="mb-4">
          <input
            type="text"
            placeholder={t('search')}
            className="px-4 py-2 border border-gray-300 rounded-md w-full max-w-md"
          />
        </div>

        <div className="text-center py-12 text-gray-500">
          <p>{t('info')}: No channels yet. Import M3U files to get started.</p>
        </div>
      </div>
    </div>
  )
}
