import './globals.css'
import Header from './components/header'
import Footer from './components/footer'

export const metadata = {
  title: 'Coudelaria Lima Monteiro',
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="pt">
      <body className="flex min-h-screen flex-col">
        <Header />
        <main className="flex-1 px-8 py-12">
          {children}
        </main>
        <Footer />
      </body>
    </html>
  )
}
