import Link from "next/link";
import { Facebook, Instagram } from "lucide-react";

export default function Footer() {
  return (
    <footer className="w-full bg-[#232540] text-[#FFFFFF] px-6 py-12">
      <div className="max-w-5xl mx-auto flex flex-col items-center gap-6 text-center">

        {/* Nome da Coudelaria */}
        <h2 className="text-xl font-semibold tracking-wide">
          Coudelaria Lima Monteiro
        </h2>

        {/* Redes sociais */}
        <div className="flex gap-6">
          <Link
            href="https://www.facebook.com/"
            target="_blank"
            aria-label="Facebook"
            className="text-[#E5E7EB] hover:text-[#16A34A] transition-colors"
          >
            <Facebook size={22} />
          </Link>

          <Link
            href="https://www.instagram.com/"
            target="_blank"
            aria-label="Instagram"
            className="text-[#E5E7EB] hover:text-[#16A34A] transition-colors"
          >
            <Instagram size={22} />
          </Link>
        </div>

        {/* Links legais */}
        <div className="flex flex-wrap justify-center gap-4 text-sm text-[#E5E7EB]">
          <Link href="/termos-condicoes" className="hover:text-[#16A34A]">
            Termos e Condições
          </Link>
          <Link href="/politica-privacidade" className="hover:text-[#16A34A]">
            Política de Privacidade
          </Link>
          <Link href="/politica-cookies" className="hover:text-[#16A34A]">
            Política de Cookies
          </Link>
        </div>

        {/* Direitos reservados */}
        <p className="text-xs text-[#E5E7EB] mt-4">
          © {new Date().getFullYear()} Coudelaria Lima Monteiro. Todos os direitos reservados.
        </p>
      </div>
    </footer>
  );
}


