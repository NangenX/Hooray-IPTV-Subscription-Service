import { useState, useCallback } from 'react'
import { useTranslation } from 'react-i18next'
import { toast } from 'sonner'
import { m3uAPI } from '@/api/services'

export default function M3UImport() {
  const { t } = useTranslation()
  const [file, setFile] = useState<File | null>(null)
  const [uploading, setUploading] = useState(false)
  const [result, setResult] = useState<any>(null)

  const handleDrop = useCallback((e: React.DragEvent) => {
    e.preventDefault()
    const droppedFile = e.dataTransfer.files[0]
    if (droppedFile && (droppedFile.name.endsWith('.m3u') || droppedFile.name.endsWith('.m3u8'))) {
      setFile(droppedFile)
    } else {
      toast.error('Please upload a valid M3U file')
    }
  }, [])

  const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const selectedFile = e.target.files?.[0]
    if (selectedFile) {
      setFile(selectedFile)
    }
  }

  const handleUpload = async () => {
    if (!file) return

    setUploading(true)
    try {
      const response = await m3uAPI.import(file)
      setResult(response.data.result)
      toast.success(t('operation_success'))
      setFile(null)
    } catch (error: any) {
      toast.error(error.response?.data?.message || t('operation_failed'))
    } finally {
      setUploading(false)
    }
  }

  return (
    <div>
      <h1 className="text-3xl font-bold mb-8">{t('m3u_import')}</h1>

      <div className="bg-white p-6 rounded-lg shadow">
        <div
          onDrop={handleDrop}
          onDragOver={(e) => e.preventDefault()}
          className="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-blue-500 transition-colors"
        >
          <input
            type="file"
            id="m3u-file"
            accept=".m3u,.m3u8"
            onChange={handleFileSelect}
            className="hidden"
          />
          
          {file ? (
            <div>
              <p className="text-lg font-medium">{file.name}</p>
              <p className="text-sm text-gray-500 mt-2">
                {(file.size / 1024 / 1024).toFixed(2)} MB
              </p>
            </div>
          ) : (
            <label htmlFor="m3u-file" className="cursor-pointer">
              <p className="text-gray-600">{t('drag_drop')}</p>
              <p className="text-sm text-gray-500 mt-2">Maximum file size: 50MB, up to 3000 channels</p>
            </label>
          )}
        </div>

        {file && (
          <div className="mt-4 flex justify-end space-x-4">
            <button
              onClick={() => setFile(null)}
              className="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
            >
              {t('cancel')}
            </button>
            <button
              onClick={handleUpload}
              disabled={uploading}
              className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
            >
              {uploading ? t('uploading') : t('upload')}
            </button>
          </div>
        )}
      </div>

      {result && (
        <div className="mt-8 bg-white p-6 rounded-lg shadow">
          <h2 className="text-xl font-semibold mb-4">{t('import_result')}</h2>
          
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
              <p className="text-sm text-gray-600">{t('total_processed')}</p>
              <p className="text-2xl font-bold">{result.total_processed}</p>
            </div>
            <div>
              <p className="text-sm text-gray-600">{t('imported')}</p>
              <p className="text-2xl font-bold text-green-600">{result.imported}</p>
            </div>
            <div>
              <p className="text-sm text-gray-600">{t('skipped')}</p>
              <p className="text-2xl font-bold text-yellow-600">{result.skipped}</p>
            </div>
            <div>
              <p className="text-sm text-gray-600">{t('errors')}</p>
              <p className="text-2xl font-bold text-red-600">{result.errors}</p>
            </div>
          </div>

          {result.error_messages && result.error_messages.length > 0 && (
            <div className="mt-4">
              <h3 className="font-medium mb-2">Error Messages:</h3>
              <ul className="text-sm text-red-600 space-y-1">
                {result.error_messages.slice(0, 10).map((msg: string, idx: number) => (
                  <li key={idx}>• {msg}</li>
                ))}
              </ul>
            </div>
          )}
        </div>
      )}
    </div>
  )
}
