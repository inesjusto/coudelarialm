export default function Header() {
  return (
    <header className="bg-gradient-to-r from-dark to-primary px-8 py-4 shadow-lg">
      <div className="mx-auto flex max-w-7xl items-center justify-between">
        <h1 className="text-xl font-semibold tracking-wide text-accent">
          Coudelaria Lima Monteiro
        </h1>

        <nav className="flex gap-6 text-sm uppercase tracking-wider">
          <a className="hover:text-accent" href="#">Início</a>
          <a className="hover:text-accent" href="#">Sobre</a>
          <a className="hover:text-accent" href="#">Cavalos</a>
          <a className="hover:text-accent" href="#">Contactos</a>
        </nav>
      </div>
    </header>
  )
}
