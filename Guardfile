guard 'shell' do
  watch('jpmailer.php'){|m|
    filename = "tests/jpmailer_test.php"
    if FileTest.exist?(filename)
      run_phpunit(filename)
    else
      puts "\n>> Not found #{filename}\n"
    end
  }

  watch(%r{^tests/(.*)\_test\.php$}) {|m| run_phpunit(m[0]) }
end

def run_phpunit(filename)
  puts "\n>> run: #{filename}\n"
  result = `phpunit --bootstrap tests/bootstrap.php #{filename}`
  result =~ /\((.+test.?,.+assertion.?)\)/
  if success = $+
    n "#{success}", File.basename(filename)
    puts ">> passed: #{success}"
  else
    result =~ /(Tests:.+Assertions:.+Failures:.+)/
    failure = $+
    n "Failed", File.basename(filename)
    puts ">> failed"
    puts ">> Error repot\n#{result}"
  end
end