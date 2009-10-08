SHpartners Web Framework - TODO
===============================

* Add support for custom regular expressions in the routes

    Sinatra does it as follows:

    `def compile(path)
      keys = []
      if path.respond_to? :to_str
        special_chars = %w{. + ( )}
        pattern =
          path.to_str.gsub(/((:\w+)|[\*#{special_chars.join}])/) do |match|
            case match
            when "*"
              keys << 'splat'
              "(.*?)"
            when *special_chars
              Regexp.escape(match)
            else
              keys << $2[1..-1]
              "([^/?&#]+)"
            end
          end
        [/^#{pattern}$/, keys]
      elsif path.respond_to?(:keys) && path.respond_to?(:match)
        [path, path.keys]
      elsif path.respond_to? :match
        [path, keys]
      else
        raise TypeError, path
      end
    end`

* Add support for multiple applications?

    This probably means rewriting part of the url router.