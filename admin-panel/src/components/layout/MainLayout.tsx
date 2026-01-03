import { Outlet, Link, useNavigate, useLocation } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { toast } from 'sonner'
import { authAPI } from '@/api/services'

export default function MainLayout() {
  const { t, i18n } = useTranslation()
  const navigate = useNavigate()
  const location = useLocation()

  const handleLogout = async () => {
    try {
      await authAPI.logout()
    } catch (error) {
      // Ignore error
    } finally {
      localStorage.removeItem('token')
      localStorage.removeItem('admin')
      navigate('/login')
    }
  }

  const changeLanguage = (lng: string) => {
    i18n.changeLanguage(lng)
    localStorage.setItem('language', lng)
    toast.success(t('success'))
  }

  const admin = JSON.parse(localStorage.getItem('admin') || '{}')

  const isActive = (path: string) => location.pathname === path || location.pathname.startsWith(path + '/')

  return (
    <div className="min-h-screen bg-gray-100">
      {/* Top Nav */}
      <nav className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16">
            <div className="flex items-center">
              <h1 className="text-xl font-bold">{t('app_name')}</h1>
            </div>
            
            <div className="flex items-center space-x-4">
              <select
                value={i18n.language}
                onChange={(e) => changeLanguage(e.target.value)}
                className="px-3 py-1 border border-gray-300 rounded-md text-sm"
              >
                <option value="en">{t('english')}</option>
                <option value="zh-CN">{t('chinese')}</option>
              </select>

              <span className="text-sm text-gray-700">
                {admin.username}
              </span>

              <button
                onClick={handleLogout}
                className="text-sm text-red-600 hover:text-red-800"
              >
                {t('logout')}
              </button>
            </div>
          </div>
        </div>
      </nav>

      <div className="flex">
        {/* Sidebar */}
        <aside className="w-64 bg-white shadow-sm min-h-[calc(100vh-64px)]">
          <nav className="p-4 space-y-2">
            <Link
              to="/"
              className={`block px-4 py-2 rounded-md ${
                isActive('/')  && location.pathname === '/'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-100'
              }`}
            >
              {t('dashboard')}
            </Link>

            <div>
              <div className="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">
                {t('channels')}
              </div>
              <Link
                to="/channels"
                className={`block px-4 py-2 rounded-md ${
                  isActive('/channels') && !location.pathname.includes('/import')
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-700 hover:bg-gray-100'
                }`}
              >
                {t('channel_list')}
              </Link>
              <Link
                to="/channels/import"
                className={`block px-4 py-2 rounded-md ${
                  isActive('/channels/import')
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-700 hover:bg-gray-100'
                }`}
              >
                {t('m3u_import')}
              </Link>
            </div>
          </nav>
        </aside>

        {/* Main Content */}
        <main className="flex-1 p-8">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
