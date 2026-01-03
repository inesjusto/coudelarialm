import Link from "next/link";

export default function Header() {
  return (
    <header className="absolute top-0 left-0 w-full z-50">
      <div className="max-w-7xl mx-auto px-6 py-5 flex items-center justify-between text-[#FFFFFF]">

        {/* Logo / Nome */}
        <Link
          href="/"
          className="text-lg font-semibold tracking-wide hover:text-[#16A34A] transition-colors"
        >
          Coudelaria Lima Monteiro
        </Link>

        {/* Navegação central */}
        <nav className="hidden md:flex gap-8 text-sm font-medium">
          <Link href="/historia" className="hover:text-[#16A34A] transition-colors">
            História
          </Link>
          <Link href="/garanhoes" className="hover:text-[#16A34A] transition-colors">
            Garanhões
          </Link>
          <Link href="/cavalos-venda" className="hover:text-[#16A34A] transition-colors">
            Cavalos à Venda
          </Link>
          <Link href="/servicos" className="hover:text-[#16A34A] transition-colors">
            Serviços
          </Link>
          <Link href="/contactos" className="hover:text-[#16A34A] transition-colors">
            Contactos
          </Link>
        </nav>

        {/* Idiomas */}
        <div className="flex items-center gap-3 text-sm">
          <button className="opacity-70 hover:opacity-100 transition-opacity">
            PT
          </button>
          <span className="opacity-40">|</span>
          <button className="opacity-70 hover:opacity-100 transition-opacity">
            EN
          </button>
        </div>

      </div>
    </header>
  );
}
